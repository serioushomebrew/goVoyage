<?php

namespace App\GoVoyage\Library;

use \Illuminate\Support\Collection;

use \Carbon\Carbon;

use \App\GoVoyage\Library\SchipholApi;
use \App\GoVoyage\Library\TransaviaApi;
use \App\GoVoyage\Library\KLMApi;

use Storage;

use App\Airport;
use App\CacheWeather;

class Flights
{
    public static function search(
        Carbon $startDate = null,
        Carbon $endDate = null,
        int $maxBudget = null,
        int $passengers = null,
        int $temperature = null
    ) : Collection {
        // Almost all values are optional, check which are not set and give them their default values
        $startDate = ($startDate === null) ? Carbon::now() : $startDate;
        $endDate = ($endDate === null) ? Carbon::now()->addYear(1) : $endDate;
        $maxBudget = ($maxBudget === null) ? 8000 : $maxBudget;
        $passengers = ($passengers === null) ? 1 : $passengers;

        // Fix request caching for improved speeds
        $hash = sha1($startDate->format('d-m-Y') . $endDate->format('d-m-Y') . $maxBudget . $passengers . $temperature);

        if (Storage::exists('search/' . $hash)) {
            return collect(json_decode(Storage::get('search/' . $hash)));
        }

        // Initialize the API's
        $klm = new KLMApi(env('KLM_API_ENDPOINT'), env('KLM_API_ID'), env('KLM_API_KEY'));
        $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_ID'), env('TRANSAVIA_API_KEY'));

        // Search for flightplans based on the budget and departure time
        // - KLM
        // - Transavia
        $flights = collect();

        $klmFlights = $klm->request('/travel/locations/cities', [
            'expand' => 'lowest-fare',
            'pageSize' => 200,
            'country' => 'NL',
            'origins' => 'AMS',
            'minDepartureDate' => $startDate->format('Y-m-d'),
            'maxBudget' => $maxBudget,
        ]);

        if ($klmFlights && $klmFlights->_embedded) {
            $flights = collect($klmFlights->_embedded)->reduce(function ($carry, $item) use (&$passengers) {
                $carry->push([
                    // Origin
                    'origin' => [
                        'code' => $item->fare->origin->code,
                        'name' => $item->fare->origin->name,
                        'description' => $item->fare->origin->description,
                    ],

                    'destination' => [
                        'code' => $item->code,
                        'name' => $item->name,
                        'description' => $item->description,
                    ],

                    'pricing' => [
                        'price' => $item->fare->amount->price * $passengers,
                        'currency' => $item->fare->amount->currency,
                    ],

                    'dates' => [
                        'departure' => Carbon::createFromFormat('Y-m-d', $item->fare->departureDate),
                        'return' => Carbon::createFromFormat('Y-m-d', $item->fare->returnDate),
                    ],

                    'custom' => [
                        // 'popularity' => $item->fare->popularity,
                    ],
                ]);
                return $carry;
            }, $flights);
        }

        $transaviaFlights = $transavia->request('/v1/flightoffers', [
            'origin' => 'AMS',
            'origindeparturedate' => $startDate->format('Ymd'),
            'destinationdeparturedate' => $endDate->format('Ymd'),
            'adults' => $passengers,
            'price' => '0-'.($maxBudget / 2), //@NOTE: $maxBudget is per flight (not retour)
            'lowestpriceperdestination' => true,
            'limit' => 200,
            'orderby' => 'Price',
        ]);
        if ($transaviaFlights) {
            // dd($transaviaFlights);
            $flights = collect($transaviaFlights->flightOffer)->reduce(function ($carry, $item) {
                // dd($carry);
                $origAirport = AirPort::where('code', $item->outboundFlight->departureAirport->locationCode)->first();
                $destAirport = AirPort::where('code', $item->inboundFlight->departureAirport->locationCode)->first();
                $carry->push([
                    'origin' => [
                        // @TODO: may need to do extra call for detailed airport info
                        'code' => $item->outboundFlight->departureAirport->locationCode,
                        'name' => $origAirport ? $origAirport['name'] : null,
                        'description' => $origAirport ? $origAirport['city'] : null,
                    ],

                    'destination' => [
                        'code' => $item->outboundFlight->arrivalAirport->locationCode,
                        'name' => $destAirport ? $destAirport['name'] : null,
                        'description' => $destAirport ? $destAirport['city'] : null,
                    ],

                    'pricing' => [
                        'price' => $item->pricingInfoSum->totalPriceAllPassengers,
                        'currency' => $item->pricingInfoSum->currencyCode,
                    ],

                    'dates' => [
                        'departure' => Carbon::createFromFormat('Y-m-d\TH:i:s', $item->outboundFlight->departureDateTime),
                        'return' => Carbon::createFromFormat('Y-m-d\TH:i:s', $item->inboundFlight->departureDateTime),
                    ],
                ]);
                return $carry;
            }, $flights);
        }
        // dd($flights);

        // Filter all flights which are longer than the return date
        $endDate->addDay(); // add support for flights on the endDate day itself
        $flights = $flights->filter(function ($item) use (&$endDate) {
            return $endDate->gt($item['dates']['return']);
        })->map(function ($item) use (&$klm) {
            $city = $item['destination']['code'];

            // Check if the weather information exists
            $weather = CacheWeather::where('origin_code', $city)->first();

            if ($weather) {
                // @TODO: Check TTL for cache removal
                $item['weather']['temp'] = $weather->temp;
            } else {
                $weatherData = $klm->request('/travel/locations/v2/cities/' . $city . '/weather', []);
                if ($weatherData) {
                    $weather = CacheWeather::create([
                        'origin_code' => $city,
                        'temp' => $weatherData->actual->temp_C,
                    ]);

                    $item['weather']['temp'] = $weather->temp;
                } else {
                    $item['weather']['temp'] = -1; // error
                }
            }
            return $item;
        })->filter(function ($item) use (&$temperature) {
            return $temperature === null || abs($item['weather']['temp'] - $temperature) < 4;
        })->slice(0, 20);

        Storage::put('search/' . $hash, $flights->toJson());

        return $flights;
    }
}
