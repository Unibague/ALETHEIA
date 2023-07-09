<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ResponseIdeal;
use App\Http\Requests\StoreResponseIdealRequest;
use App\Http\Requests\UpdateResponseIdealRequest;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class ResponseIdealController extends Controller
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
     * @param  \App\Http\Requests\StoreResponseIdealRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreResponseIdealRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function show(ResponseIdeal $responseIdeal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function edit(ResponseIdeal $responseIdeal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateResponseIdealRequest  $request
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateResponseIdealRequest $request, ResponseIdeal $responseIdeal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ResponseIdeal  $responseIdeal
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResponseIdeal $responseIdeal)
    {
        //
    }


    public function viewEditTeachingLadders($teachingLadder){


        return Inertia::render('ResponseIdeals/Edit', ['teachingLadder' => $teachingLadder]);

    }





    public function upsertData(Request $request): JsonResponse{

        try {

            $teachingLadder = $request->input('teachingLadder');
            $unitIdentifier = $request->input('unit');
            $competences = json_encode($request->input('competences'));

            ResponseIdeal::saveResponseIdeals($teachingLadder,$competences,$unitIdentifier);

        } catch (JsonException $e) {

            return response()->json(['message' => 'Ha ocurrido un error guardando la información']);
        }

        return response()->json(['message' => 'Ideales de respuesta guardados exitosamente']);

    }


    public function getCompetences(Request $request): JsonResponse
    {
        $teachingLadder = $request->input('teachingLadder');
        $unitIdentifier = $request->input('unitIdentifier');

        return ResponseIdeal::getResponseIdeals($teachingLadder, $unitIdentifier);

    }


    public function getAllCompetences(): JsonResponse
    {

        return ResponseIdeal::getAllResponseIdeals();


    }

}
