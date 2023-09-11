<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyAssessmentPeriodRequest;
use App\Http\Requests\SetActiveAssessmentPeriodRequest;
use App\Models\AssessmentPeriod;
use App\Http\Requests\StoreAssessmentPeriodRequest;
use App\Http\Requests\UpdateAssessmentPeriodRequest;
use App\Models\Group;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class  AssessmentPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(AssessmentPeriod::all());
    }

    public function setActive(SetActiveAssessmentPeriodRequest $request, AssessmentPeriod $assessmentPeriod): JsonResponse
    {
        //Detect previous assessment period
        try {
            $active = AssessmentPeriod::getActiveAssessmentPeriod();
            $active->active = false;
            $active->timestamps = false;
            $active->save();
        } catch (\Exception $e) {
        } finally {
            $assessmentPeriod->active = true;
            $assessmentPeriod->save();
        }
        return response()->json(['message' => 'Se ha seleccionado el periodo de evaluación como el nuevo periodo de evaluación activo']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAssessmentPeriodRequest $request
     * @return JsonResponse
     */
    public function store(StoreAssessmentPeriodRequest $request): JsonResponse
    {
        AssessmentPeriod::create($request->all());
        return response()->json(['message' => 'Periodo de evaluación se ha creado exitosamente']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAssessmentPeriodRequest $request
     * @param AssessmentPeriod $assessmentPeriod
     * @return JsonResponse
     */
    public function update(UpdateAssessmentPeriodRequest $request, AssessmentPeriod $assessmentPeriod): JsonResponse
    {
        $assessmentPeriod->update($request->all());
        return response()->json(['message' => 'Periodo de evaluación actualizado exitosamente']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AssessmentPeriod $assessmentPeriod
     * @return JsonResponse
     */
    public function destroy(DestroyAssessmentPeriodRequest $request, AssessmentPeriod $assessmentPeriod): JsonResponse
    {
        if ($assessmentPeriod->active === 1) {
            return response()->json(['message' => 'No se puede eliminar un periodo de evaluación activo'], 400);
        }
        try {
            $assessmentPeriod->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === "23000") {
                return response()->json(['message' => 'No puedes eliminar un periodo de evaluación si este tiene periodos académicos asociados.'], 400);
            }
        }
        return response()->json(['message' => 'Periodo de evaluación eliminado exitosamente']);

    }


    public function show(AssessmentPeriod $assessmentPeriod)
    {
        //
    }


    public function getSuitableTeachingLadders (){

        return response()->json(AssessmentPeriod::getActiveAssessmentPeriod()->getSuitableTeachingLaddersWithNames());


    }




}
