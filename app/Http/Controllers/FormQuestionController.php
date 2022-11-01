<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormQuestion;
use App\Http\Requests\StoreFormQuestionRequest;
use App\Http\Requests\UpdateFormQuestionRequest;
use Illuminate\Http\JsonResponse;

class FormQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @throws \JsonException
     */
    public function storeOrUpdate(StoreFormQuestionRequest $request, Form $form)
    {
        $questions = $request->input('questions');
        FormQuestion::updateOrCreate(
            ['form_id' => $form->id],
            [
                'questions' => json_encode($questions, JSON_THROW_ON_ERROR)
            ]);
        return response()->json(['message' => 'Opciones de respuesta actualizadas exitosamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param FormQuestion $formQuestion
     */
    public function show(FormQuestion $formQuestion)
    {
        //
    }

    public function getByFormId(Form $form): JsonResponse
    {
        $formQuestions = $form->formQuestions;
        if (!$formQuestions) {
            return response()->json(null, 404);
        }
        return response()->json($form->formQuestions);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param FormQuestion $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(FormQuestion $formQuestion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FormQuestion $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormQuestion $formQuestion)
    {
        //
    }
}
