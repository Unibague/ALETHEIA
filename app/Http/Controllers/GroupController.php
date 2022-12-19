<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetGroupsRequest;
use App\Models\Group;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param GetGroupsRequest $request
     * @return JsonResponse
     */
    public function index(GetGroupsRequest $request): JsonResponse
    {
        return response()->json(Group::with(['teacher', 'serviceArea', 'academicPeriod'])->get());
    }

    public function sync()
    {
        return response()->json(['message' => 'Servicio no disponible']);

        $url = 'http://integra.unibague.edu.co/groups';
        $curl = new CurlCobain($url);
        $curl->setQueryParamsAsArray([
            'year' => '23',
            'api_token' => env('MIDDLEWARE_API_TOKEN')
        ]);
        $curl->setQueryParam('year', '23');
        $request = $curl->makeRequest();
        try {
            $academicPeriods = json_decode($request, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        //Iterate over received data and create the academic period
        foreach ($academicPeriods as $academicPeriod) {
            AcademicPeriod::updateOrCreate(
                [
                    'name' => $academicPeriod->name
                ],
                [
                    'description' => $academicPeriod->description,
                    'class_start_date' => $academicPeriod->class_start_date,
                    'class_end_date' => $academicPeriod->class_end_date,
                ]);
        }
        return response()->json(['message' => 'Los periodos se han sincronizado exitosamente']);

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
     * @param \App\Http\Requests\StoreGroupRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGroupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Group $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Group $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateGroupRequest $request
     * @param \App\Models\Group $group
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Group $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        //
    }
}
