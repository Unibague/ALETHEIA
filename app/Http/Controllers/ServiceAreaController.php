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
    public function index()
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreServiceAreaRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceAreaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ServiceArea $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceArea $serviceArea)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ServiceArea $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceArea $serviceArea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateServiceAreaRequest $request
     * @param \App\Models\ServiceArea $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceAreaRequest $request, ServiceArea $serviceArea)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ServiceArea $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyServiceAreaRequest $request, ServiceArea $assessmentPeriod)
    {
        //
    }
}
