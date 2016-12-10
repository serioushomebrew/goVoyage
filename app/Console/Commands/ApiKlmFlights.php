<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApiKlmFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:klm-flights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve the KLM flights data, based on some parameters';

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
        // @TODO: actually read out command parameters
        $client = new \GuzzleHttp\Client();

        $endpoint = 'https://www.klm.com/oauthcust/oauth/token';

        $response = $client->request('POST', $endpoint, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic Zm5iM2RtdXBhajZ3ZXh5dGE5dmF2YnZwOnljV0M2cnd1eHg=',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $token = json_decode($response->getBody()->getContents());

        // get flights by date and maxBudget
        // @TODO: get data from parameters
        $startDate = "2016-12-20";
        $endDate = "2017-02-01";
        $maxBudget = 125;

        $endpoint = 'https://api.klm.com/travel/locations/cities';
        $params = '?expand=lowest-fare&pageSize=2&country=NL&origins=AMS&minDepartureDate='.$startDate.'&maxDepartureDate='.$endDate.'&maxBudget='.$maxBudget;

        $response = $client->request('GET', $endpoint.$params, [
            'headers' => [
                'Authorization' => 'Bearer '.$token->access_token,
            ],
        ]);

        $flights = json_decode($response->getBody()->getContents())->_embedded;
        foreach ($flights as $flight) {
            echo "Flight to: ".$flight->code." ".$flight->name.", Price: ".$flight->fare->amount->price." ".$flight->fare->amount->currency."\n";
            // @TODO: find weather data for flight
        }

        // dd([
        //     'statusCode' => $response->getStatusCode(),
        //     'body' => json_decode($response->getBody()->getContents())->_embedded,
        // ]);


    }
}
