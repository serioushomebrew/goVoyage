<?php

namespace App\GoVoyage\Library;

use \GuzzleHttp\Client as HttpClient;

class SchipholApi
{
    /**
     * The endpoint HTTP API location
     * @var string
     */
    protected $endpoint = null;

    /**
     * The Schiphol Api id used to authorize
     * @var string
     */
    protected $apiId = null;

    /**
     * The Schiphol Api key used to authorize
     * @var string
     */
    protected $apiKey = null;

    /**
     * The Guzzle Client
     * @var \Guzzle\Client
     */
    protected $client = null;

    /**
     * Create the Schiphol Api handler
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
     * Send a api request to the Schiphol API service
     *
     * For example $schiphol->request('/public-flights/flights', [
     *     'includedelays' => false,
     * ]);
     *
     * @param  string $command The location of the API
     * @param  array  $params  Optional parameters for the request
     * @return string          The response from the api service
     */
    public function request(string $command, array $params = null)
    {
        $params = http_build_query(array_merge([
            'app_id' => $this->apiId,
            'app_key' => $this->apiKey,
        ], $params));

        // @TODO: There has to be a PHP method for this, right?
        $endpoint = $this->endpoint . $command . '?' . $params;

        try {
            $response = $this->client->request('GET', $endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'ResourceVersion' => 'v3',
                ],
                'connect_timeout' => 10,
            ]);
        } catch (\Exception $e) {
            // @TODO: What to do here?
            // throw new \Exception($e);
            return null;
        }

        // @TODO: Handle errors
        return json_decode($response->getBody()->getContents());
    }
}
