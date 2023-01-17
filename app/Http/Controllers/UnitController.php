<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\DestroyUnityRequest;
use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use Database\Seeders\UnitSeeder;
use App\Models\AssessmentPeriod;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Ospina\CurlCobain\CurlCobain;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Unit::getCurrentUnits());
    }

    public function sync(): JsonResponse
    {
        try {
            $units = AtlanteProvider::get('units');
            Unit::createOrUpdateFromArray($units);
        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()],500);
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
