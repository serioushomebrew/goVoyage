<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\GoVoyage\Library\TransaviaApi;

class TransaviaFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'govoyage:transaviaflights
        {startDate? : The starting date yyyyMMdd}
        {endDate? : The ending date yyyyMMdd}
        {priceRange? : The price range for all passengers 0-100}
        {adultPassengers? : Amount of adult passengers 1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search flights offered by Transavia';

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
        $startDate = $this->argument('startDate');
        while (!preg_match('/[0-9]{6,10}/', $startDate)) {
            $startDate = $this->ask('Start date (yyyyMMdd)');
        }

        // Fetch the ending date
        $endDate = $this->argument('endDate');
        while (!preg_match('/[0-9]{6,10}/', $endDate)) {
            $endDate = $this->ask('End date (yyyyMMdd)');
        }

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

        $transavia = new TransaviaApi(env('TRANSAVIA_API_ENDPOINT'), env('TRANSAVIA_API_CONSUMERKEY'), env('TRANSAVIA_API_CONSUMERSECRET'));

        $res = $transavia->request('/v1/flightoffers', [
            'origin' => 'AMS',
            'origindeparturedate' => '20161220',
            'destinationdeparturedate' => '20170120',
            'adults' => 1,
            'price' => '0-100',
            'lowestpriceperdestination' => true,
            'limit' => '1000',
            'orderby' => 'Price',
        ]);

        dd(json_decode($res));
    }
}
