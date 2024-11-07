<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Faculty::with('serviceAreas')->orderBy('name')->get());
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $faculty = Faculty::create([
            'name' => $request->input('name'),
        ]);
        $serviceAreas = [];
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        foreach ($request->input('serviceAreas') as $code) {
            $serviceAreas [] = ['service_area_code' => $code, 'assessment_period_id' => $activeAssessmentPeriodId];
        }

        $faculty->serviceAreas()->attach($serviceAreas);
        return response()->json(['message' => 'Faculty created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faculty $faculty)
    {
        $faculty = Faculty::findOrFail($faculty->id);
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $serviceAreas = [];

        foreach ($request->input('serviceAreas') as $code) {
            $serviceAreas [] = ['service_area_code' => $code, 'assessment_period_id' => $activeAssessmentPeriodId];
        }

        $faculty->serviceAreas()->sync($serviceAreas);
        return response()->json(['message' => 'Facultad actualizada exitosamente'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faculty $faculty)
    {
        // Detach related service areas to avoid foreign key constraint issues
        $faculty->serviceAreas()->detach();

        // Now safely delete the faculty record
        $faculty->delete();

        return response()->json(['message' => 'Facultad borrada exitosamente'], 201);


    }
}
