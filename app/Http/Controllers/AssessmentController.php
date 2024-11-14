<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;

class AssessmentController extends Controller
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

    public function hasStudentFinishedAssessment(Request $request)
    {
        $academicPeriodName = $request->query('periodo'); // Retrieves the 'keyword' query parameter
        $userEmail = $request->query('email');
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $user = \DB::table('users')->where('email','=', $userEmail)->first();

        if(!$user){
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $academicPeriod = \DB::table('academic_periods')->where('name','=',$academicPeriodName)->first();

        if (!$academicPeriod){
            return response()->json(['message' => 'Periodo académico no encontrado'], 404);
        }

        $hasStudentFinishedAssessment = \DB::table('students_completed_assessment_audit')->where('user_id','=',$user->id)
            ->where('academic_period_id','=',$academicPeriod->id)->where('assessment_period_id','=',$activeAssessmentPeriodId)->first() == true;

        return response()->json($hasStudentFinishedAssessment);
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
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
