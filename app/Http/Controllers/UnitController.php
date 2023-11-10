<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Http\Requests\DestroyUnityRequest;
use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use App\Models\TeacherProfile;
use App\Models\UnityAssessment;
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
    public function index(Request $request): JsonResponse
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(Unit::getCurrentUnits($assessmentPeriodId));
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


    public function syncStaffMembers(): JsonResponse
    {
        try {

            $staffMembers = $this->getStaffMembersFromEndpoint();
            $staffMembers = array_filter($staffMembers, function ($staffMember) {
                return $staffMember->mail != "";
            });

            Unit::createOrUpdateStaffMembersUsers($staffMembers);

        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos']);
        }

        return response()->json(['message' => 'Los funcionarios se han sincronizado exitosamente']);

    }

    public function getStaffMembersFromEndpoint()
    {
        $url = 'https://directorio.unibague.edu.co/modules/mod_directorio/get_Funcionaries.php';
        $curl = new CurlCobain($url);
        return json_decode($curl->makeRequest());
    }

    public function getStaffMembersFromDB()
    {

        return response()->json(Unit::getStaffMembers());

    }


    public function assign(): JsonResponse
    {
        try {
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

        $roleId = DB::table('roles')->where('name', $roleName)->pluck('id')[0];

        $userIsAlreadyAdmin = DB::table('role_user')->where('user_id', $userId)->where('role_id', $roleId)->exists();


        if (!$userIsAlreadyAdmin) {

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

        //Aqui simplemente eliminamos al usuario de esa unidad con el role de admin de unidad.
        DB::table('v2_unit_user')->where('user_id', $userId)
            ->where('unit_identifier', $unitId)->where('role_id', $adminRoleId)->delete();


        //Ya aquí estamos comprobando si ese usuario sigue siendo admin en otra unidad cualquiera...
        $user = DB::table('v2_unit_user')
            ->where('user_id', $userId)->where('role_id', $adminRoleId)->get();

        //Si entra aquí es porque ya no es admin en niguna otra... entonces le quitamos ese role en la tabla de role_user
        if ($user->count() == 0) {

            DB::table('role_user')->where('user_id', $userId)->where('role_id', $adminRoleId)->delete();

        }

        return response()->json(['message' => 'Adminstrador de unidad eliminado exitosamente']);
    }


    public function deleteUnitBoss(Request $request): JsonResponse

    {
        $unitId = $request->input('unitIdentifier');
        $userId = $request->input('userId');
        $unitBossRole= Role::getRoleIdByName('jefe de profesor');


        $alreadyDoneAssessments = UnityAssessment::where('evaluator_id',$userId)
            ->where('role', 'jefe')->where('unit_identifier', $unitId)
            ->where('pending', 0)->first();

        if ($alreadyDoneAssessments){

            return response()->json(['message' => 'Ese jefe ya tiene asignaciones realizadas en esta unidad, confirmas que deseas eliminarlo?'], 500);

        }

        //Aqui simplemente eliminamos al usuario de esa unidad con el role de jefe de docente
        DB::table('v2_unit_user')->where('user_id', $userId)
            ->where('unit_identifier', $unitId)->where('role_id', $unitBossRole)->delete();


        UnityAssessment::where('evaluator_id',$userId)
            ->where('role', 'jefe')->where('unit_identifier', $unitId)->delete();


        //Ya aquí estamos comprobando si ese usuario sigue siendo jefe de docente en otra unidad cualquiera...
        $user = DB::table('v2_unit_user')
            ->where('user_id', $userId)->where('role_id', $unitBossRole)->get();

        //Si entra aquí es porque ya no es jefe de docente en niguna otra... entonces le quitamos ese role en la tabla de role_user
        if ($user->count() == 0) {

            DB::table('role_user')->where('user_id', $userId)->where('role_id', $unitBossRole)->delete();

        }

        return response()->json(['message' => 'Jefe de docente eliminado exitosamente']);
    }

    public function confirmDeleteUnitBoss(Request $request): JsonResponse

    {
        $unitId = $request->input('unitIdentifier');
        $userId = $request->input('userId');
        $unitBossRole= Role::getRoleIdByName('jefe de profesor');

        //Aqui simplemente eliminamos al usuario de esa unidad con el role de jefe de docente
        DB::table('v2_unit_user')->where('user_id', $userId)
            ->where('unit_identifier', $unitId)->where('role_id', $unitBossRole)->delete();


        UnityAssessment::where('evaluator_id',$userId)
            ->where('role', 'jefe')->where('unit_identifier', $unitId)->delete();


        //Ya aquí estamos comprobando si ese usuario sigue siendo jefe de docente en otra unidad cualquiera...
        $user = DB::table('v2_unit_user')
            ->where('user_id', $userId)->where('role_id', $unitBossRole)->get();

        //Si entra aquí es porque ya no es jefe de docente en niguna otra... entonces le quitamos ese role en la tabla de role_user
        if ($user->count() == 0) {

            DB::table('role_user')->where('user_id', $userId)->where('role_id', $unitBossRole)->delete();

        }

        return response()->json(['message' => 'Jefe de docente eliminado exitosamente']);
    }


    public function assignUnitBoss(Request $request): JsonResponse
    {
        $unitId = $request->input('unitIdentifier');
        $userId = $request->input('userId');
        $roleName = $request->input('role');

        $roleId = DB::table('roles')->where('name', $roleName)->first()->id;

        $userIsAlreadyBoss = DB::table('role_user')->where('user_id', $userId)->where('role_id', $roleId)->exists();


        if (!$userIsAlreadyBoss) {

            DB::table('role_user')->updateOrInsert(['user_id' => $userId, 'role_id' => $roleId]);

        }

        Unit::assignTeacherAsUnitBoss($unitId, $userId, $roleId);

        return response()->json(['message' => 'Adminstrador de unidad actualizado exitosamente']);

    }


    public function getUnitAdmin(Request $request): JsonResponse
    {
        $unitId = $request->input('unitId');
        return response()->json(Unit::getUnitAdmin($unitId));

    }


    public function transferTeacherToUnit(Request $request): JsonResponse
    {
        $unit = $request->input('unitIdentifier');
        $userId = $request->input('userId');
        $teacherRole = Role::getTeacherRoleId();

        $user = DB::table('v2_unit_user')->where([['user_id', $userId], ['unit_identifier', $unit], ['role_id', $teacherRole]])->first();

        if ($user) {
            return response()->json(['message' => 'El docente ya se encuentra en la unidad a la que desea transferir'], 500);
        }

        /*//Aqui verificamos si a ese docente ya se le asignó un jefe de docente, si ya se le asignó entonces borramos
        esa asignación y después de eso sí procedemos a transferirlo*/

        $actualUnit = DB::table('v2_unit_user')->where('user_id', $userId)
            ->where('role_id',$teacherRole)->select('unit_identifier')->first()->unit_identifier;

        $teacherAlreadyHasAssignments= UnityAssessment::where('evaluated_id', $userId)
            ->where('role', 'jefe')->where('unit_identifier', $actualUnit)->first();

        if($teacherAlreadyHasAssignments){

            UnityAssessment::where('evaluated_id', $userId)
                ->where('role', 'jefe')->where('unit_identifier', $actualUnit)->delete();

        }
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
            'identifier' => $code . '-' . $assessmentPeriodAsString,
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

    public function getUnitTeachers($unitIdentifier): JsonResponse
    {
        return response()->json(Unit::getUnitTeachers($unitIdentifier));
    }

    public function getUnitAdminsAndBosses($unitIdentifier): JsonResponse
    {
        return response()->json(Unit::getUnitAdminsAndBosses($unitIdentifier));
    }

    public function getUnitBosses($unitIdentifier): JsonResponse
    {
        return response()->json(Unit::getUnitBosses($unitIdentifier));
    }

    public function getUnitAdmins($unitIdentifier): JsonResponse
    {
        return response()->json(Unit::getUnitAdmins($unitIdentifier));
    }

    public function getAssignedTeachers(Request $request): JsonResponse
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        return response()->json(Unit::getAssignedTeachers($assessmentPeriodId));
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
        return response()->json(['message' => 'Unidad actualizada correctamente']);
    }

    public function edit(Unit $unit)
    {
        return Inertia::render('Unities/ManageUnity', ['unit' => $unit]);
    }

    public function manageRoles(Unit $unit)
    {
        return Inertia::render('Unities/AssignUnitRoles', ['unit' => $unit]);
    }


    public function assessmentStatus(Unit $unit)
    {
        return Inertia::render('Unities/UnitAssessmentStatus', ['unit' => $unit]);
    }


    public function getSuitableTeachers(): JsonResponse {
        return response()->json(Unit::getUnitTeachersSuitableForAssessment());
    }


    public function getAllFaculties(): JsonResponse {
        return response()->json(Unit::getFaculties());
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
        if($unit->users->count()>0){
            return response()->json(['message' => 'No puedes eliminar una unidad con usuarios adentro'], 400);
        }

        if ($unit->is_custom === 1) {
            $unit->delete();
            return response()->json(['message' => 'Unidad eliminada exitosamente']);
        }
        return response()->json(['message' => 'No se ha podido eliminar, la unidad no es personalizada'], 400);
    }

}


