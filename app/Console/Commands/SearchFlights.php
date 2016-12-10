<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \Carbon\Carbon;

use \App\GoVoyage\Library\SchipholApi;
use \App\GoVoyage\Library\KLMApi;

class SearchFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'govoyage:searchflights
        {--w|wizzard : Run the inline wizzard}
        {--s|startDate= : The starting date dd-mm-yyyy}
        {--e|endDate= : The ending date dd-mm-yyyy}
        {--b|maxBudget= : The maximum allowed price}
        {--p|passengers= : The ammount of passengers}
        {--t|temperature= : The temperature in degrees celcius}';

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
        if ($this->option('wizzard')) {
            do {
                $startDate = $this->ask('What is the starting date (Using date format dd-mm-yyyy)');
            } while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $startDate));

            do {
                $endDate = $this->ask('What is the end date (Using date format dd-mm-yyyy)');
            } while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $endDate));

            do {
                $maxBudget = $this->ask('What is your budget? (User 0 for no budget)');
            } while (!is_numeric($maxBudget));

            do {
                $passengers = $this->ask('How many passengers?');
            } while (!is_numeric($passengers) || $passengers < 1);

            do {
                $temperature = $this->ask('What is your desired temperature in degrees celcius?');
            } while (!is_numeric($temperature));
        } else {
            $startDate = $this->option('startDate');
            $startDate = Carbon::createFromFormat('d-m-Y', $startDate);

            $endDate = $this->option('endDate');
            $endDate = Carbon::createFromFormat('d-m-Y', $endDate);

            $maxBudget = $this->option('maxBudget');

            $passengers = $this->option('passengers');

            $temperature = $this->option('temperature');
        }


        // $startDate = $this->option('startDate');
        // while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $startDate)) {
        //     if ($this->option('wizzard') === false) {
        //         throw
        //     }
        //
        //     $startDate = $this->ask('Start date (dd-mm-yyyy)');
        // }
        //
        // // $startDate = $this->option('start-date')
        //
        // dd($this->option('wizzard'));

        // $startDate = $this->argument('startDate');
        // while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $startDate)) {
        //
        // }
        //
        //
        //
        //
        //
        //
        // if ($this->argument('--wizzard'))









        // // Fetch the starting date
        // $startDate = $this->argument('startDate');
        // while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $startDate)) {
        //     $startDate = $this->ask('Start date (dd-mm-yyyy)');
        // }
        // $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
        //
        // // Fetch the ending date
        // $endDate = $this->argument('endDate');
        // while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $endDate)) {
        //     $endDate = $this->ask('End date (dd-mm-yyyy)');
        // }
        // $endDate = Carbon::createFromFormat('d-m-Y', $endDate);
        //
        // dd(123);
        //
        // // Fetch the max price
        // $maxPrice = $this->argument('maxPrice');
        // if (!$maxPrice) {
        //     $maxPrice = $this->ask('Budget limit');
        // }
        //
        // //
        // //
        // //
        //
        // // Fetch all flights from KLM
        // $klm = new KLMApi(env('KLM_API_ENDPOINT'), env('KLM_API_ID'), env('KLM_API_KEY'));
        //
        // $flights = $klm->request('/travel/locations/cities', [
        //     'expand' => 'lowest-fare',
        //     'pageSize' => 4000000,
        //     'country' => 'NL',
        //     'origins' => 'AMS',
        //     'minDepartureDate' => $startDate->format('Y-m-d'),
        //     'maxBudget' => $maxPrice,
        // ]);
        //
        //
        // if ($flights) {
        //     collect($flights->_embedded)->filter(function ($item) {
        //         return true;
        //     })->each(function ($item) {
        //         $this->info('Country: ' . $item->parent->name);
        //         $this->info('Location: ' . $item->name);
        //         $this->info('Description: ' . $item->description);
        //         $this->info('Price: ' . $item->fare->amount->price . ' ' . $item->fare->amount->currency);
        //         $this->info('Departure: ' . $item->fare->departureDate);
        //         $this->info('Return: ' . $item->fare->returnDate);
        //         $this->info('----------------------------------------');
        //     });
        //
        //     dd(count($flights->_embedded));
        // }

    }
}
