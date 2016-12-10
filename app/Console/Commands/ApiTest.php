<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApiTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing the api connection';

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
        $client = new \GuzzleHttp\Client();

        $endpoint = 'https://api-acc.schiphol.nl/public-flights/destinations?app_id=e0900540&app_key=7d58ec89ef8838f03ea8c2a5d091a5ff&page=0&sort=%2BpublicName';

        $response = $client->request('GET', $endpoint, [
            'headers' => [
                'Accept' => 'application/json',
                'ResourceVersion' => 'v1',
            ],
        ]);

        dd([
            'statusCode' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents(),
        ]);
    }
}
