<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \App\GoVoyage\Library\SchipholApi;
use \App\GoVoyage\Library\TransaviaApi;
use \App\GoVoyage\Library\KLMApi;

class SearchFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'govoyage:searchflights
        {startDate? : The starting date yyyyMMdd}
        {endDate? : The ending date yyyyMMdd}
        {priceRange? : The price range for all passengers 0-100}
        {adultPassengers? : Amount of adult passengers 1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search flights based on search criteria';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Fetch the starting date
        // $startDate = $this->argument('startDate');
        // while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $startDate)) {
        //     $startDate = $this->ask('Start date (dd-mm-yyyy)');
        // }
        //
        // // Fetch the ending date
        // $endDate = $this->argument('endDate');
        // while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $endDate)) {
        //     $endDate = $this->ask('End date (dd-mm-yyyy)');
        // }
        //
        // $schiphol = new SchipholApi(env('SCHIPHOL_API_ENDPOINT'), env('SCHIPHOL_API_ID'), env('SCHIPHOL_API_KEY'));
        //
        // $res = $schiphol->request('/public-flights/flights', [
        //     'fromdate' => '2016-12-11',
        //     // 'includedelays' => false,
        // ]);

        // Fetch the price range
        $priceRange = $this->argument('priceRange');
        while (!preg_match('/[0-9]+-[0-9]+/', $priceRange)) {
            $priceRange = $this->ask('Price range (0-100)');
        }

        // Fetch the price range
        $adultPassengers = $this->argument('adultPassengers');
        while (!preg_match('/[0-9]+/', $adultPassengers)) {
            $adultPassengers = $this->ask('Adult passengers (123)');
        }

        // Fetch the starting date
        $startDate = $this->argument('startDate');
        while (!preg_match('/[0-9]{6,10}/', $startDate)) {
            $startDate = $this->ask('Start date (yyyyMMdd)');
        }

        // Fetch the ending date
        $endDate = $this->argument('endDate');
        while (!preg_match('/[0-9]{6,10}/', $endDate)) {
            $endDate = $this->ask('End date (yyyyMMdd)');
        }

        $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_CONSUMERKEY'), env('TRANSAVIA_API_CONSUMERSECRET'));

        $res = $transavia->request('/v1/flightoffers', [
            'origin' => 'AMS',
            'origindeparturedate' => $startDate,
            'destinationdeparturedate' => $endDate,
            'adults' => $adultPassengers,
            'price' => $priceRange,
            'lowestpriceperdestination' => true,
            'limit' => '1000',
            'orderby' => 'Price',
        ]);
        // $transaviaFlights = $res->

        // $klm = new KLMApi(env('KLM_API_ENDPOINT'), env('KLM_API_ID'), env('KLM_API_KEY'));
        //
        // $res = $klm->request('/travel/locations/cities', [
        //     'expand' => 'lowest-face',
        //     'pageSize' => 2,
        //     'country' => 'NL',
        //     'origins' => 'AMS',
        //     // ''
        // ]);

        dd($res);
        // dd(json_decode($res));
    }
}
