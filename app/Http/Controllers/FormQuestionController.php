<?php

namespace App\Http\Controllers;

use App\Models\FormQuestion;
use App\Http\Requests\StoreFormQuestionRequest;
use App\Http\Requests\UpdateFormQuestionRequest;

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
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFormQuestionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFormQuestionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FormQuestion  $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function show(FormQuestion $formQuestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FormQuestion  $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(FormQuestion $formQuestion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFormQuestionRequest  $request
     * @param  \App\Models\FormQuestion  $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFormQuestionRequest $request, FormQuestion $formQuestion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FormQuestion  $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormQuestion $formQuestion)
    {
        //
    }
}
