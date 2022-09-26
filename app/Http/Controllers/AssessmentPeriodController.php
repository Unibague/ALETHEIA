<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Http\Requests\StoreAssessmentPeriodRequest;
use App\Http\Requests\UpdateAssessmentPeriodRequest;

class AssessmentPeriodController extends Controller
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
     * @param  \App\Http\Requests\StoreAssessmentPeriodRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAssessmentPeriodRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AssessmentPeriod  $assessmentPeriod
     * @return \Illuminate\Http\Response
     */
    public function show(AssessmentPeriod $assessmentPeriod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AssessmentPeriod  $assessmentPeriod
     * @return \Illuminate\Http\Response
     */
    public function edit(AssessmentPeriod $assessmentPeriod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAssessmentPeriodRequest  $request
     * @param  \App\Models\AssessmentPeriod  $assessmentPeriod
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAssessmentPeriodRequest $request, AssessmentPeriod $assessmentPeriod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssessmentPeriod  $assessmentPeriod
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssessmentPeriod $assessmentPeriod)
    {
        //
    }
}
