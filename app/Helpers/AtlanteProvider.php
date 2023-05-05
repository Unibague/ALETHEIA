<?php

namespace App\Helpers;

use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Ospina\CurlCobain\CurlCobain;

class AtlanteProvider
{
    public $baseUrl;
    public $endpoint;
    public $queryParams;
    public $curl;

    /**
     * @param $endpoint
     * @param $queryParams
     */
    public function __construct($endpoint, $queryParams = null)
    {
        $this->baseUrl = env('APP_ENV') === 'production' ?
            'http://integra.unibague.edu.co/' : 'http://a-t-l-a-n-t-e.test/';
        $this->endpoint = $endpoint;
        $apiToken = env('APP_ENV') === 'production' ? env('MIDDLEWARE_API_TOKEN') : env('MIDDLEWARE_API_TOKEN_TEST');
        if ($queryParams !== null) {
            $this->queryParams = array_merge(['api_token' => $apiToken], $queryParams);
        } else {
            $this->queryParams = ['api_token' => $apiToken];
        }
        $this->curl = new CurlCobain($this->buildUrl());
    }

    private function buildUrl(): string
    {
        return $this->baseUrl . $this->endpoint;
    }

    /**
     * @throws \JsonException
     */
    public static function get($endpoint, $queryParams = null, $asArray = false)
    {
        $self = new self($endpoint, $queryParams);
        $self->curl->setQueryParamsAsArray($self->queryParams);
        $request = $self->curl->makeRequest();
        return json_decode($request, $asArray, 512, JSON_THROW_ON_ERROR);
    }

}
