<?php

namespace App\GoVoyage\Library;

use \Illuminate\Support\Collection;

use \Carbon\Carbon;

use \App\GoVoyage\Library\SchipholApi;
use \App\GoVoyage\Library\TransaviaApi;
use \App\GoVoyage\Library\KLMApi;

use App\CacheWeather;

class Flights
{
    public static function search(
        Carbon $startDate,
        Carbon $endDate,
        int $maxBudget,
        int $passengers,
        int $temperature
    ) : Collection {
        // Search for flights
        $flights = collect();

        $klm = new KLMApi(env('KLM_API_ENDPOINT'), env('KLM_API_ID'), env('KLM_API_KEY'));
        $klmFlights = $klm->request('/travel/locations/cities', [
            'expand' => 'lowest-fare',
            'pageSize' => 2000,
            'country' => 'NL',
            'origins' => 'AMS',
            'minDepartureDate' => $startDate->format('Y-m-d'),
            'maxBudget' => $maxBudget,
        ]);

        if ($klmFlights && $klmFlights->_embedded) {
            $flights = collect($klmFlights->_embedded)->reduce(function ($carry, $item) {
                // dd($item);
                // dd($carry);

                $carry->push([
                    // Origin
                    'origin' => [
                        'code' => $item->fare->origin->code,
                        'name' => $item->fare->origin->name,
                        'description' => $item->fare->origin->description,
                    ],

                    // Destination
                    'destination' => [
                        'code' => $item->code,
                        'name' => $item->name,
                        'description' => $item->description,
                    ],

                    // Pricing
                    'pricing' => [
                        'price' => $item->fare->amount->price,
                        'currency' => $item->fare->amount->currency,
                    ],

                    // Dates
                    'dates' => [
                        'departure' => Carbon::createFromFormat('Y-m-d', $item->fare->departureDate),
                        'return' => Carbon::createFromFormat('Y-m-d', $item->fare->returnDate),
                    ],

                    // Custom, KLM specific extra data
                    'custom' => [
                        // 'popularity' => $item->fare->popularity,
                    ],
                ]);
                return $carry;
            }, $flights);
        }
        // dd($flights);

        $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_ID'), env('TRANSAVIA_API_KEY'));
        $transaviaFlights = $transavia->request('/v1/flightoffers', [
            'origin' => 'AMS',
            'origindeparturedate' => $startDate->format('Ymd'),
            'destinationdeparturedate' => $endDate->format('Ymd'),
            'adults' => $passengers,
            'price' => '0-'.($maxBudget / 2), //@NOTE: $maxBudget is per flight (not retour)
            'lowestpriceperdestination' => true,
            'limit' => 1000,
            'orderby' => 'Price',
        ]);
        if ($transaviaFlights) {
            // dd($transaviaFlights);
            $flights = collect($transaviaFlights->flightOffer)->reduce(function ($carry, $item) {
                // dd($carry);
                $carry->push([
                    // Origin
                    'origin' => [
                        // @TODO: may need to do extra call for detailed airport info
                        'code' => $item->outboundFlight->departureAirport->locationCode,
                        'name' => null,
                        'description' => null,
                    ],

                    // Destination
                    'destination' => [
                        'code' => $item->outboundFlight->arrivalAirport->locationCode,
                        'name' => null,
                        'description' => null,
                    ],

                    // Pricing
                    'pricing' => [
                        'price' => $item->pricingInfoSum->totalPriceAllPassengers,
                        'currency' => $item->pricingInfoSum->currencyCode,
                    ],

                    // Dates
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
            return abs($item['weather']['temp'] - $temperature) < 4;
        });

        return $flights;
    }
}
