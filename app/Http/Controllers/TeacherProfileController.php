<?php

namespace App\Http\Controllers;

use App\Models\TeacherProfile;
use App\Http\Requests\StoreTeacherProfileRequest;
use App\Http\Requests\UpdateTeacherProfileRequest;

class TeacherProfileController extends Controller
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
     * @param  \App\Http\Requests\StoreTeacherProfileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTeacherProfileRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TeacherProfile  $teacherProfile
     * @return \Illuminate\Http\Response
     */
    public function show(TeacherProfile $teacherProfile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TeacherProfile  $teacherProfile
     * @return \Illuminate\Http\Response
     */
    public function edit(TeacherProfile $teacherProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTeacherProfileRequest  $request
     * @param  \App\Models\TeacherProfile  $teacherProfile
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeacherProfileRequest $request, TeacherProfile $teacherProfile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TeacherProfile  $teacherProfile
     * @return \Illuminate\Http\Response
     */
    public function destroy(TeacherProfile $teacherProfile)
    {
        //
    }
}
