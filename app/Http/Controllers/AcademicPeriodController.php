<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Http\Requests\StoreAcademicPeriodRequest;
use App\Http\Requests\UpdateAcademicPeriodRequest;
use Database\Seeders\AcademicPeriodSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JsonException;
use Ospina\CurlCobain\CurlCobain;

class AcademicPeriodController extends Controller
{

    /**
     * @throws \JsonException
     */
    public function sync()
    {
        $url = 'http://integra.unibague.edu.co/academicPeriods';
        $curl = new CurlCobain($url);
        $curl->setQueryParamsAsArray([
            'year' => '23',
            'api_token' => env('MIDDLEWARE_API_TOKEN')
        ]);
        $curl->setQueryParam('year', '23');
        $request = $curl->makeRequest();
        try {
            $academicPeriods = json_decode($request, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        //Iterate over received data and create the academic period
        foreach ($academicPeriods as $academicPeriod) {
            AcademicPeriod::updateOrCreate(
                [
                    'name' => $academicPeriod->name
                ],
                [
                    'class_start_date' => $academicPeriod->class_start_date,
                    'class_end_date' => $academicPeriod->class_end_date,
                ]);
        }
        return response()->json(['message' => 'Los periodos se han sincronizado exitosamente']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(AcademicPeriod::with('assessmentPeriod')->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAcademicPeriodRequest $request
     * @param AcademicPeriod $academicPeriod
     * @return JsonResponse
     */
    public function update(UpdateAcademicPeriodRequest $request, AcademicPeriod $academicPeriod): JsonResponse
    {
        $academicPeriod->update($request->all());
        return response()->json(['message' => 'El periodo ha sido actualizado exitosamente']);
    }
}
