<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use Database\Seeders\UnitySeeder;
use App\Models\AssessmentPeriod;
use App\Models\Unity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UnityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Unity::with('users')->get());
    }

    public function createFakeUnities(): string
    {
        (new \Database\Seeders\UnitySeeder)->run();
        return 'Fake unity created';
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreUnityRequest $request
     * @return Response
     */
    public function store(StoreUnityRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *

     * @param Unity $unity
     * @return Response
     */
    public function show(Unity $unity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUnityRequest $request
     * @param Unity $unity
     * @return JsonResponse
     */
    public function update(UpdateUnityRequest $request, Unity $unity): JsonResponse
    {
        $unity->update($request->all());
        return response()->json(['message' => 'Unidad actualizadas correctamente']);
    }

    public function edit(Unity $unity)
    {

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param Unity $unity
     * @return JsonResponse
     */
    public function destroy(Unity $unity): JsonResponse
    {
        if ($unity->is_custom == 1) {
            $unity->delete();
            return response()->json(['message' => 'Unidad eliminada correctamente']);
        } else {
            return response()->json(['message' => 'No se ha podido eliminar, la unidad no es personalizada'], 400);
        }
    }
}
