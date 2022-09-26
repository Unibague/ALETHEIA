<?php

namespace App\Http\Controllers;

use App\Models\FormAnswers;
use App\Http\Requests\StoreFormAnswersRequest;
use App\Http\Requests\UpdateFormAnswersRequest;

class FormAnswersController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFormAnswersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFormAnswersRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FormAnswers  $formAnswers
     * @return \Illuminate\Http\Response
     */
    public function show(FormAnswers $formAnswers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FormAnswers  $formAnswers
     * @return \Illuminate\Http\Response
     */
    public function edit(FormAnswers $formAnswers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFormAnswersRequest  $request
     * @param  \App\Models\FormAnswers  $formAnswers
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFormAnswersRequest $request, FormAnswers $formAnswers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FormAnswers  $formAnswers
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormAnswers $formAnswers)
    {
        //
    }
}
