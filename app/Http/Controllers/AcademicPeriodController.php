<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Models\AcademicPeriod;
use App\Http\Requests\StoreAcademicPeriodRequest;
use App\Http\Requests\UpdateAcademicPeriodRequest;
use Database\Seeders\AcademicPeriodSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use JsonException;
use Ospina\CurlCobain\CurlCobain;

class AcademicPeriodController extends Controller
{

    public function sync(): JsonResponse
    {

        try {
            $academicPeriods = AtlanteProvider::get('academicPeriods');
        } catch (JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }
        AcademicPeriod::createOrUpdateFromArray($academicPeriods);
        return response()->json(['message' => 'Los periodos se han sincronizado exitosamente']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        if ($request->input('active') === 'true') {
            return response()->json(AcademicPeriod::getCurrentAcademicPeriods());
        }
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
