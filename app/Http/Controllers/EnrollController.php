<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\GetGroupsRequest;
use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\Enroll;
use App\Models\Group;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param GetGroupsRequest $request
     * @return JsonResponse
     */
    public function index(GetGroupsRequest $request): JsonResponse
    {
        return response()->json(Enroll::getActiveEnrolls());
    }

    public function sync(): JsonResponse
    {
        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

        $user = auth()->user();

        foreach ($academicPeriods as $academicPeriod) {
            try {
                $enrolls = AtlanteProvider::get('enrolls', [
                    'periods' => $academicPeriod->name,
                ], true);

                Enroll::createOrUpdateFromArray($enrolls, $academicPeriod->id);
            } catch (\JsonException $e) {
                return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos: ' . $e->getMessage()],500);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()],400);
            }
        }

        return response()->json(['message' => "Estudiantes sincronizados exitosamente"]);
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


    public function deleteThoseExistingDuplicatedGroups(){

        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

        foreach ($academicPeriods as $academicPeriod) {

            $enrolls = AtlanteProvider::get('enrolls', [
                'periods' => $academicPeriod->name,
            ], true);

            Enroll::deleteThoseDuplicatedGroups($enrolls, $academicPeriod->id);
        }
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
