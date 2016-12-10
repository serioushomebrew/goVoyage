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

        // $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_ID'), env('TRANSAVIA_API_KEY'));
        // $transaviaFlights = $transavia->request('/v1/flightoffers', [
        //     'origin' => 'AMS',
        //     'origindeparturedate' => $startDate->format('Ymd'),
        //     'destinationdeparturedate' => $endDate->format('Ymd'),
        //     'adults' => 1,
        //     'price' => '0-1000',
        //     'lowestpriceperdestination' => true,
        //     'limit' => '1000',
        //     'orderby' => 'Price',
        // ]);
        //
        // if ($transaviaFlights && $transaviaFlights->flightOffer) {
        //     $flights = collect($transaviaFlights->flightOffer)->reduce(function ($carry, $item) {
        //         // dd($item);
        //         $carry->push([
        //             'origin_code' => $item->outboundFlight->departureAirport->locationCode,
        //             'origin_name' => null,
        //             'origin_description' => null,
        //             'country_code' => null,
        //             'country_name' => null,
        //             'country_description' => null,
        //             'departure_date' => null,
        //             'return_date' => null,
        //             'price' => null,
        //             'currency' => null,
        //         ]);
        //         return $carry;
        //     }, $flights);
        // }

        // Filter all flights which are longer than the return date
        $flights = $flights->filter(function ($item) use (&$endDate) {
            return $endDate->lt($item['dates']['return']);
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
                    $item['weather']['temp'] = 99;
                }
            }

            return $item;
        })->filter(function ($item) use (&$temperature) {
            return abs($item['weather']['temp'] - $temperature) < 4;
        });

        return $flights;
    }
}
