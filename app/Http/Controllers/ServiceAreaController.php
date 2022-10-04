<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyServiceAreaRequest;
use App\Models\ServiceArea;
use App\Http\Requests\StoreServiceAreaRequest;
use App\Http\Requests\UpdateServiceAreaRequest;

class ServiceAreaController extends Controller
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
     * @param  \App\Http\Requests\StoreServiceAreaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceAreaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceArea $serviceArea)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceArea $serviceArea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServiceAreaRequest  $request
     * @param  \App\Models\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceAreaRequest $request, ServiceArea $serviceArea)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyServiceAreaRequest $request,ServiceArea $assessmentPeriod)
    {
        //
    }
}
