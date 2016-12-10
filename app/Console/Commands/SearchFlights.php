<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \App\GoVoyage\Library\SchipholApi;

class SearchFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'govoyage:searchflights';

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
        $schiphol = new SchipholApi(env('SCHIPHOL_API_ENDPOINT'), env('SCHIPHOL_API_ID'), env('SCHIPHOL_API_KEY'));

        $res = $schiphol->request('/public-flights/flights', [
            'includedelays' => false,
        ]);

        dd($res);
    }
}
