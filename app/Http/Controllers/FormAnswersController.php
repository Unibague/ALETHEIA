<?php

namespace App\Http\Controllers;

use App\Models\FormAnswers;
use App\Http\Requests\StoreFormAnswersRequest;
use App\Http\Requests\UpdateFormAnswersRequest;
use Illuminate\Http\JsonResponse;

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

    public function getTeacherAnswers(): JsonResponse
    {
        return response()->json(FormAnswers::getCurrentTeacherFormAnswers());
    }


    public function getStudentPerspectiveAnswers(): JsonResponse
    {
        return response()->json(FormAnswers::getCurrentTeacherFormAnswersFromStudents());
    }


    public function getFinalGrades(): JsonResponse
    {
        return response()->json(FormAnswers::getFinalGradesFromTeachers());
    }

}
