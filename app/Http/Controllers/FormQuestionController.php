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
     * @throws \JsonException
     */
    public function storeOrUpdate(StoreFormQuestionRequest $request, Form $form): JsonResponse
    {
        $questions = $request->input('questions');
        $form->update(
            [
                'questions' => json_encode($questions, JSON_THROW_ON_ERROR)
            ]);
        return response()->json(['message' => 'Opciones de respuesta actualizadas exitosamente']);
    }


    public function getByFormId(Form $form)
    {
        return response($form->questions);
    }


}
