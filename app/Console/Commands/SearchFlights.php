<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        {startDate? : The starting date dd-mm-yyyy}
        {endDate? : The ending date dd-mm-yyyy}';

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
        $startDate = $this->argument('startDate');
        while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $startDate)) {
            $startDate = $this->ask('Start date (dd-mm-yyyy)');
        }

        // Fetch the ending date
        $endDate = $this->argument('endDate');
        while (!preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{4}/', $endDate)) {
            $endDate = $this->ask('End date (dd-mm-yyyy)');
        }

        $klm = new KLMApi(env('KLM_API_ENDPOINT'), env('KLM_API_ID'), env('KLM_API_KEY'));

        $res = $klm->request('/travel/locations/cities', [
            'expand' => 'lowest-face',
            'pageSize' => 2,
            'country' => 'NL',
            'origins' => 'AMS',
            // ''
        ]);

        // $schiphol = new SchipholApi(env('SCHIPHOL_API_ENDPOINT'), env('SCHIPHOL_API_ID'), env('SCHIPHOL_API_KEY'));
        //
        // $res = $schiphol->request('/public-flights/flights', [
        //     'fromdate' => '2016-12-11',
        //     // 'includedelays' => false,
        // ]);

        dd($res);

        dd(json_decode($res));
    }
}
