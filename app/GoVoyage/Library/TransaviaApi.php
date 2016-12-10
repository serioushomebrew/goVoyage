<?php

namespace App\GoVoyage\Library;

use \GuzzleHttp\Client as HttpClient;

class TransaviaApi
{
    /**
     * The endpoint HTTP API location
     * @var string
     */
    protected $endpoint = null;

    /**
     * The Transavia Api id (Consumer Key) used to authorize
     * @var string
     */
    protected $apiId = null;

    /**
     * The Transavia Api key (Consumer Secret) used to authorize
     * @var string
     */
    protected $apiKey = null;

    /**
     * The Guzzle Client
     * @var \Guzzle\Client
     */
    protected $client = null;

    /**
     * Create the Transavia Api handler
     * @param string $apiId  [description]
     * @param string $apiKey [description]
     */
    public function __construct(string $endpoint, string $apiId, string $apiKey)
    {
        $this->endpoint = $endpoint;
        $this->apiId = $apiId;
        $this->apiKey = $apiKey;

        $this->client = new HttpClient();
    }

    /**
     * Send a api request to the Transavia API service
     *
     * For example $transavia->request('/v1/flightoffers', [
     *     'origin' => 'AMS',
     *     'origindeparturedate' => '20161220',
     *     'destinationdeparturedate' => '20170120',
     *     'adults' => 1,
     *     'price' => '0-100',
     *     'lowestpriceperdestination' => true,
     *     'limit' => '1000',
     *     'orderby' => 'Price',
     * ]);
     *
     *
     * @param  string $command The location of the API
     * @param  array  $params  Optional parameters for the request
     * @return string          The response from the api service
     */
    public function request(string $command, array $params = null)
    {
        $params = http_build_query($params);

        // @TODO: There has to be a PHP method for this, right?
        $endpoint = $this->endpoint . $command . '?' . $params;

        try {
            $response = $this->client->request('GET', $endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $this->apiId,
                ],
                'connect_timeout' => 10,
            ]);
        } catch (\Exception $e) {
            // @TODO: What to do here?
            throw new \Exception($e);
            return null;
        }

        // @TODO: Handle errors
        return json_decode($response->getBody()->getContents());
    }
}
