<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyServiceAreaRequest;
use App\Models\AssessmentPeriod;
use App\Models\ServiceArea;
use App\Http\Requests\StoreServiceAreaRequest;
use App\Http\Requests\UpdateServiceAreaRequest;
use Illuminate\Http\JsonResponse;
use Ospina\CurlCobain\CurlCobain;

class ServiceAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(ServiceArea::all());
    }

    public function sync(): JsonResponse
    {
        $url = 'http://integra.unibague.edu.co/serviceAreas';
        $curl = new CurlCobain($url);
        $curl->setQueryParamsAsArray([
            'api_token' => env('MIDDLEWARE_API_TOKEN')
        ]);
        $request = $curl->makeRequest();
        try {
            $serviceAreas = json_decode($request, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }
        //Iterate over received data and create the academic period
        foreach ($serviceAreas as $serviceArea) {
            ServiceArea::updateOrCreate(
                [
                    'code' => $serviceArea->code
                ],
                [
                    'name' => $serviceArea->name,
                    'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
                ]);
        }
        return response()->json(['message' => 'Las áreas de service se han sincronizado exitosamente']);
    }
}
