<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\DestroyUnityRequest;
use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use App\Models\TeacherProfile;
use Database\Seeders\UnitSeeder;
use App\Models\AssessmentPeriod;
use App\Models\Unit;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Js;
use Inertia\Inertia;
use Ospina\CurlCobain\CurlCobain;


class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {

        return response()->json(Unit::getCurrentUnits());
    }

    public function sync(): JsonResponse
    {
        try {
            $units = AtlanteProvider::get('units');

            Unit::createOrUpdateFromArray($units);
        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Las unidades se han sincronizado exitosamente']);
    }



    public function syncStaffMembers(): JsonResponse{

        try{

            $staffMembers = $this->getStaffMembersFromEndpoint();

            $staffMembers = array_filter($staffMembers, function ($staffMember) {

                return $staffMember->mail != "";

            });

            Unit::createOrUpdateStaffMembersUsers($staffMembers);

        }

        catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        return response()->json(['message' => 'Los funcionarios se han sincronizado exitosamente']);

    }

    public function getStaffMembersFromEndpoint() {

        $url = 'https://directorio.unibague.edu.co/modules/mod_directorio/get_Funcionaries.php';

        $curl = new CurlCobain($url);

        return json_decode($curl->makeRequest());

    }

    public function getStaffMembersFromDB() {


        return response()->json(Unit::getStaffMembers());

    }



    public function assign(): JsonResponse
    {
        try{
            Unit::assignTeachersToUnits();

        } catch (\Exception $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos'], 500);
        }

        return response()->json(['message' => 'Docentes asignados exitosamente']);

    }


    public function assignUnitAdmin(Request $request): JsonResponse
    {
        $unitId = $request->input('unitIdentifier');
        $userId = $request->input('userId');
        $roleName = $request->input('role');

        $roleId = DB::table('roles')->where('name',$roleName)->pluck('id')[0];

        $userIsAlreadyAdmin = DB::table('role_user')->where('user_id',$userId)->where('role_id',$roleId)->exists();


        if(!$userIsAlreadyAdmin){

            DB::table('role_user')->updateOrInsert(['user_id' => $userId, 'role_id' => $roleId]);

        }

            Unit::assignStaffMemberAsUnitAdmin($unitId, $userId, $roleId);

        return response()->json(['message' => 'Adminstrador de unidad actualizado exitosamente']);

    }


    public function deleteUnitAdmin(Request $request): JsonResponse

    {
        $unitId = $request->input('unitIdentifier');
        $userId = $request->input('userId');
        $adminRoleId = Role::getUnitAdminRoleId();

        DB::table('v2_unit_user')->where('user_id', $userId)
            ->where('unit_identifier', $unitId)->where('role_id', $adminRoleId)->delete();

        $user = DB::table('v2_unit_user')
            ->where('user_id',$userId)->where('role_id', $adminRoleId)->get();

        if($user->count() == 0){

            DB::table('role_user')->where('user_id',$userId)->where('role_id',$adminRoleId)->delete();

        }
/*
        if ($unit->is_custom === 1) {
            $unit->delete();
            return response()->json(['message' => 'Unidad eliminada exitosamente']);
        }*/
        return response()->json(['message' => 'Adminstrador de unidad eliminado exitosamente']);
    }




    public function getUnitAdmin(Request $request): JsonResponse{

        $unitId = $request->input('unitId');

        return response()->json(Unit::getUnitAdmin($unitId));

    }


    public function transferTeacherToUnit(Request $request): JsonResponse
    {
        $unit = $request->input('unitIdentifier');
        $userId = $request->input('userId');

        $user = DB::table('v2_unit_user')->where([['user_id', $userId], ['unit_identifier', $unit]])->first();

        if ($user) {
            return response()->json(['message' => 'El docente ya se encuentra en la unidad a la que desea transferir'], 500);
        }

/*        $user = DB::table('unity_assessments')
            ->where('evaluated_id',$userId)->where('evaluator_id', $userId)->first();*/


        Unit::transferTeacherToSelectedUnit($unit, $userId);

        return response()->json(['message' => 'Docente transferido exitosamente']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUnityRequest $request
     * @return JsonResponse
     */
    public function store(StoreUnityRequest $request): JsonResponse
    {

        $assessmentPeriodAsString = (string)AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $code = 'custom-' . $request->input('name');


        Unit::create([
            'name' => $request->input('name'),
            'code' => $code,
            'is_custom' => 1,
            'identifier' => $code. '-' . $assessmentPeriodAsString,
            'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
        ]);

        return response()->json(['message' => 'Unidad creada exitosamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param Unit $unit
     * @return Response
     */
    public function show($unitIdentifier): JsonResponse
    {

        return response()->json(Unit::getUnitInfo($unitIdentifier));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUnityRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function update(UpdateUnityRequest $request, Unit $unit): JsonResponse
    {

        $unit->update($request->all());
        return response()->json(['message' => 'Unidad actualizadas correctamente']);
    }

    public function edit($unit)
    {

        $unit = Unit::where('identifier', '=', $unit)->get();

        return Inertia::render('Unities/ManageUnity', ['unit' => $unit]);


    }


    public function manageRoles($unitId)
    {


        $unitId = Unit::where('identifier', '=', $unitId)->get();

        return Inertia::render('Unities/AssignUnitRoles', ['unitId' => $unitId]);


    }


    public function assessmentStatus($unitId)
    {


        $unitId = Unit::where('identifier', '=', $unitId)->get();

        return Inertia::render('Unities/UnitAssessmentStatus', ['unitId' => $unitId]);


    }


    public function getSuitableTeachers(): JsonResponse {

        return response()->json(Unit::getUnitTeachersSuitableForAssessment());

    }





    /**
     *
     * Remove the specified resource from storage.
     *
     * @param DestroyUnityRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */

    public function destroy(DestroyUnityRequest $request, Unit $unit): JsonResponse

    {

        if ($unit->is_custom === 1) {
            $unit->delete();
            return response()->json(['message' => 'Unidad eliminada exitosamente']);
        }
        return response()->json(['message' => 'No se ha podido eliminar, la unidad no es personalizada'], 400);
    }








}


