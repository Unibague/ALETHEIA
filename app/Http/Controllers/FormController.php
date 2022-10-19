<?php

namespace App\Http\Controllers;

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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFormRequest $request
     * @return JsonResponse
     */
    public function store(StoreFormRequest $request)
    {
        if($request->input('type') === 'estudiantes'){
            Form::createStudentForm($request);
            return response()->json(['message' => 'Formulario creado exitosamente']);
        }
        Form::createOthersForm($request);

        return response()->json(['message' => 'Formulario creado exitosamente']);
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
     * Show the form for editing the specified resource.
     *
     * @param Form $form
     * @return Response
     */
    public function edit(Form $form)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateFormRequest $request
     * @param Form $form
     * @return Response
     */
    public function update(UpdateFormRequest $request, Form $form)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(DestroyFormRequest $request, Form $form)
    {
        if (count($form->formAnswers) !== 0) {
            return response()->json(['message' => 'No puedes borrar un formulario con respuestas']);
        }

        return response()->json(['message' => 'Formulario borrado']);
    }
}
