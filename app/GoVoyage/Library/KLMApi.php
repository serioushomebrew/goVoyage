<?php

namespace App\GoVoyage\Library;

use \GuzzleHttp\Client as HttpClient;

class KLMApi
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
     * The bearer token
     * @var string
     */
    protected $bearerToken = null;

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

        // @TODO: Replace this with OAuth2
        $response = $this->client->request('POST', 'https://www.klm.com/oauthcust/oauth/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic Zm5iM2RtdXBhajZ3ZXh5dGE5dmF2YnZwOnljV0M2cnd1eHg=',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $this->bearerToken = json_decode($response->getBody()->getContents())->access_token;
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
        $params = http_build_query($params);

        // @TODO: There has to be a PHP method for this, right?
        $endpoint = $this->endpoint . $command . '?' . $params;

        try {
            $response = $this->client->request('GET', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->bearerToken,
                ],
                'connect_timeout' => 10,
            ]);
        } catch (\Exception $e) {
            // @TODO: What to do here?
            // throw new \Exception($e);
            return null;
        }

        // @TODO: Handle errors
        return $response->getBody()->getContents();
    }
}
