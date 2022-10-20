<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Ospina\CurlCobain\CurlCobain;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @throws \JsonException
     */
    public function makeRequest(string $endpoint, array $params = [])
    {
        $url = 'http://integra.unibague.edu.co/';
        $curl = new CurlCobain($url . $endpoint);
        $params['api_token'] = env('MIDDLEWARE_API_TOKEN');
        $params['consulta'] = 'Consultar';
        $curl->setQueryParamsAsArray($params);
        $request = $curl->makeRequest();
        return json_decode($request, false, 512, JSON_THROW_ON_ERROR);

    }


}
