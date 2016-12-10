<?php

namespace App\GoVoyage\Library;

use \Illuminate\Support\Collection;

use \Carbon\Carbon;

use \App\GoVoyage\Library\SchipholApi;
use \App\GoVoyage\Library\TransaviaApi;
use \App\GoVoyage\Library\KLMApi;

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
                $carry->push([
                    'origin_code' => $item->fare->origin->code,
                    'origin_name' => $item->fare->origin->name,
                    'origin_description' => $item->fare->origin->description,
                    'country_code' => $item->parent->code,
                    'country_name' => $item->parent->name,
                    'country_description' => $item->parent->description,
                    'departure_date' => Carbon::createFromFormat('Y-m-d', $item->fare->departureDate),
                    'return_date' => Carbon::createFromFormat('Y-m-d', $item->fare->returnDate),
                    'price' => $item->fare->amount->price,
                    'currency' => $item->fare->amount->currency,
                ]);
                return $carry;
            }, $flights);
        }

        $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_ID'), env('TRANSAVIA_API_KEY'));
        $transaviaFlights = $transavia->request('/v1/flightoffers', [
            'origin' => 'AMS',
            'origindeparturedate' => $startDate->format('Ymd'),
            'destinationdeparturedate' => $endDate->format('Ymd'),
            'adults' => 1,
            'price' => '0-1000',
            'lowestpriceperdestination' => true,
            'limit' => '1000',
            'orderby' => 'Price',
        ]);

        if ($transaviaFlights && $transaviaFlights->flightOffer) {
            $flights = collect($transaviaFlights->flightOffer)->reduce(function ($carry, $item) {
                // dd($item);
                $carry->push([
                    'origin_code' => $item->outboundFlight->departureAirport->locationCode,
                    'origin_name' => null,
                    'origin_description' => null,
                    'country_code' => null,
                    'country_name' => null,
                    'country_description' => null,
                    'departure_date' => null,
                    'return_date' => null,
                    'price' => null,
                    'currency' => null,
                ]);
                return $carry;
            }, $flights);
        }

        return $flights;
    }
}
