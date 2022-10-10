<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyUnityRequest;
use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use Database\Seeders\UnitSeeder;
use App\Models\AssessmentPeriod;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Ospina\CurlCobain\CurlCobain;

class UnityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Unit::with('users')->get());
    }

    public function sync(): JsonResponse
    {
        $url = 'http://integra.unibague.edu.co/units';
        $curl = new CurlCobain($url);
        $curl->setQueryParamsAsArray([
            'api_token' => env('MIDDLEWARE_API_TOKEN')
        ]);
        $request = $curl->makeRequest();
        try {
            $units = json_decode($request, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        //Iterate over received data and create the academic period
        foreach ($units as $unit) {
            Unit::updateOrCreate(
                [
                    'code' => $unit->code
                ],
                [
                    'name' => $unit->name,
                    'is_custom' => 0,
                    'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
                ]);
        }
        return response()->json(['message' => 'Las unidades se han sincronizado exitosamente']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUnityRequest $request
     * @return JsonResponse
     */
    public function store(StoreUnityRequest $request): JsonResponse
    {
        Unit::create([
            'name' => $request->input('name'),
            'code' => 'custom-' . $request->input('name'),
            'is_custom' => 1,
            'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
        ]);

        return response()->json(['message' => 'Unidad creada exitosamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param Unit $unit
     * @return Response
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUnityRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function update(UpdateUnityRequest $request, Unit $unit): JsonResponse
    {
        $unit->update($request->all());
        return response()->json(['message' => 'Unidad actualizadas correctamente']);
    }

    public function edit(Unit $unit)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyUnityRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */

    public function destroy(DestroyUnityRequest $request, Unit $unit): JsonResponse

    {
        if ($unit->is_custom === 1) {
            $unit->delete();
            return response()->json(['message' => 'Unidad eliminada exitosamente']);
        }
        return response()->json(['message' => 'No se ha podido eliminar, la unidad no es personalizada'], 400);
    }
}
