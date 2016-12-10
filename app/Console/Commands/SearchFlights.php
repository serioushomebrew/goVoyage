<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \Carbon\Carbon;

use App\GoVoyage\Library\Flights;

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
        // Fetch the input data
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

        dd(Flights::search($startDate, $endDate, $maxBudget, $passengers, $temperature));

        // echo json_encode($flights);
    }
}
