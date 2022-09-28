<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetActiveAssessmentPeriodRequest;
use App\Models\AssessmentPeriod;
use App\Http\Requests\StoreAssessmentPeriodRequest;
use App\Http\Requests\UpdateAssessmentPeriodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AssessmentPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return
     */
    public function index()
    {
        return response()->json(AssessmentPeriod::all());
    }

    public function setActive(SetActiveAssessmentPeriodRequest $request, AssessmentPeriod $assessmentPeriod)
    {
        //Detect previous assessment period
        try {
            $active = AssessmentPeriod::getActiveAssessmentPeriod();
            $active->active = false;
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
     * Display the specified resource.
     *
     * @param AssessmentPeriod $assessmentPeriod
     * @return Response
     */
    public function show(AssessmentPeriod $assessmentPeriod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param AssessmentPeriod $assessmentPeriod
     * @return Response
     */
    public function edit(AssessmentPeriod $assessmentPeriod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAssessmentPeriodRequest $request
     * @param AssessmentPeriod $assessmentPeriod
     * @return JsonResponse
     */
    public function update(UpdateAssessmentPeriodRequest $request, AssessmentPeriod $assessmentPeriod)
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
    public function destroy(AssessmentPeriod $assessmentPeriod): JsonResponse
    {
        $assessmentPeriod->delete();
        return response()->json(['message' => 'Periodo de evaluación eliminado exitosamente']);

    }
}
