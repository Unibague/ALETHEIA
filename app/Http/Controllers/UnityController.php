<?php

namespace App\Http\Controllers;

use App\Models\Unity;
use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use Illuminate\Http\JsonResponse;

class UnityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Unity::all());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUnityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUnityRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Unity  $unity
     * @return \Illuminate\Http\Response
     */
    public function show(Unity $unity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUnityRequest  $request
     * @param  \App\Models\Unity  $unity
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUnityRequest $request, Unity $unity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Unity  $unity
     * @return \Illuminate\Http\Response
     */
    public function destroy(Unity $unity)
    {
        //
    }
}
