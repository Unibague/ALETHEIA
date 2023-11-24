<?php

namespace App\Http\Controllers;

use App\Models\FormAnswers;
use App\Http\Requests\StoreFormAnswersRequest;
use App\Http\Requests\UpdateFormAnswersRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormAnswersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(FormAnswers::getCurrentFormAnswers());
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FormAnswers $formAnswers
     * @return JsonResponse
     */
    public function show(FormAnswers $answer)
    {
        return response()->json($answer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FormAnswers $formAnswers
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormAnswers $formAnswers)
    {
        //
    }

    public function getTeacherAnswers(Request $request): JsonResponse
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(FormAnswers::getCurrentTeacherFormAnswers($assessmentPeriodId));
    }

    public function getStudentAnswers(Request $request): JsonResponse
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(FormAnswers::getCurrentTeacherFormAnswersFromStudents($assessmentPeriodId));
    }

    public function getFinalGrades(Request $request): JsonResponse
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(FormAnswers::getFinalGradesFromTeachers($assessmentPeriodId));
    }

    public function getOpenAnswersColleagues(Request $request): JsonResponse
    {
        $teacherId = $request->input('teacherId');
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(FormAnswers::getOpenAnswersFromColleagues($teacherId, $assessmentPeriodId));
    }

    public function getOpenAnswersStudents(Request $request): JsonResponse
    {
        $teacherId = $request->input('teacherId');
        $serviceArea = $request->input('serviceArea');
        $assessmentPeriodId = $request->input('assessmentPeriodId');

        if (!$serviceArea){
            return response()->json(FormAnswers::getOpenAnswersFromStudents360Report($teacherId, $assessmentPeriodId));
        }

        return response()->json(FormAnswers::getOpenAnswersFromStudentsSingleServiceAreaReport($teacherId, $serviceArea, $assessmentPeriodId));
    }

    public function getOpenAnswersStudentsFromGroup(Request $request): JsonResponse
    {
        $teacherId = $request->input('teacherId');
        $serviceArea = $request->input('serviceArea');
        $groupId = $request->input('groupId');
        $assessmentPeriodId = $request->input('assessmentPeriodId');

        return response()->json(FormAnswers::getOpenAnswersFromStudentsGroupsReport($teacherId, $serviceArea, $groupId,$assessmentPeriodId));
    }


}
