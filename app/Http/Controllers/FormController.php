<?php

namespace App\Http\Controllers;

use App\Http\Requests\CopyFormRequest;
use App\Http\Requests\DestroyFormRequest;
use App\Models\Form;
use App\Http\Requests\StoreFormRequest;
use App\Http\Requests\UpdateFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Form::with(['academicPeriod', 'assessmentPeriod', 'unit', 'serviceArea'])->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFormRequest $request
     * @return JsonResponse
     */
    public function store(StoreFormRequest $request): JsonResponse
    {
        Form::create($request->all());
        return response()->json(['message' => 'Formulario creado exitosamente']);
    }

    public function copy(CopyFormRequest $request, Form $form): JsonResponse
    {
        $newForm = $form->replicate(['name']);
        $newForm->name = 'Copia de ' . $form->name;
        $newForm->save();

        return response()->json(['message' => 'Formulario copiado exitosamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param Form $form
     * @return Response
     */
    public function show(Form $form)
    {
        dd('hola');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateFormRequest $request
     * @param Form $form
     * @return JsonResponse
     */
    public function update(UpdateFormRequest $request, Form $form): JsonResponse
    {
        $form->update($request->all());
        return response()->json(['message' => 'Formulario actualizado exitosamente']);

    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(DestroyFormRequest $request, Form $form): JsonResponse
    {
        if (count($form->formAnswers) !== 0) {
            return response()->json(['message' => 'No puedes borrar un formulario con respuestas']);
        }
        $form->delete();
        return response()->json(['message' => 'Formulario borrado']);
    }
}
