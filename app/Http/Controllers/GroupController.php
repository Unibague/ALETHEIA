<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\GetGroupsRequest;
use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\Group;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;

class GroupController extends Controller
{

    public function getWithoutTeacher(): JsonResponse
    {
        return response()->json(Group::withoutTeacher());
    }

    public function getEnrolls(int $groupId): JsonResponse
    {
        $group = Group::where('group_id', '=', $groupId)
            ->with('enrolls')
            ->first();
        return response()->json($group);
    }

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

    public function sync(): JsonResponse
    {
        try {
            $namesSeparatedByCommas = AcademicPeriod::getCurrentAcademicPeriodsByCommas();
            $groups = AtlanteProvider::get('groups', [
                'periods' => $namesSeparatedByCommas,
            ], true);

            foreach ($groups as $key => $group) {
                if ($groups[$key]['teacher_email'] == "") {
                    $groups[$key]['teacher_email'] = null;
                }
            }


            Group::createOrUpdateFromArray($groups, explode(',', $namesSeparatedByCommas));
        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos: ' . $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()], 400);
        }
        return response()->json(['message' => 'Grupos sincronizados exitosamente']);
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
