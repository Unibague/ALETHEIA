<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Table;

/**
 * App\Models\Unity
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $is_custom
 * @property int $assessment_period_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentPeriod $assessmentPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Form[] $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UnityAssessment[] $unityAssessment
 * @property-read int|null $unity_assessment_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\UnityFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Unit extends Model
{
    protected $table = 'v2_units';
    protected $guarded = [];
    protected $primaryKey = 'identifier';
    public $incrementing = false;
    protected $keyType = 'string';
    use HasFactory;

    public static function getCurrentUnits(int $assessmentPeriodId = null){

        if ($assessmentPeriodId == null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return self::where('assessment_period_id','=', $assessmentPeriodId)->with('teachersFromUnit')->get();
    }

    public static function createOrUpdateFromArray(array $units): void
    {
        $upsertData = [];
        $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        foreach ($units as $unit) {
            $unitAsString = (string)$unit->code;
            $assessmentPeriodAsString = (string)$assessmentPeriodId;
            $identifier = $unitAsString.'-'.$assessmentPeriodAsString;

            $upsertData[] = [
                'identifier' => $identifier,
                'code' => $unit->code,
                'name' => $unit->name,
                'is_custom' => 0,
                'assessment_period_id' => $assessmentPeriodId
            ];

            DB::table('v2_units')->upsert($upsertData, $identifier, ['name', 'is_custom', 'assessment_period_id']);
        }
    }

    public static function transferTeacherToSelectedUnit($unit, $userId, $teacherRole): void{

        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod()->id;

//        dd($unit, $userId, $teacherRole);

        DB::table('v2_unit_user')->updateOrInsert(
            ['user_id' => $userId, 'role_id' => $teacherRole],
            ['unit_identifier' => $unit, 'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]
        );

        UnityAssessment::updateOrInsert(['evaluated_id' => $userId, 'evaluator_id'=> $userId,
            'role' => 'autoevaluación', 'assessment_period_id'=> $activeAssessmentPeriod],
            ['unit_identifier' => $unit]);
    }


    public static function getUnitInfo($unitIdentifier){
        return self::where('identifier', $unitIdentifier)->with('users.teacherProfile')->first();
    }

    public static function getUnitTeachers($unitIdentifier){
        return self::where('identifier', $unitIdentifier)->with('teachersFromUnit.teacherProfile')->first();
    }

    public static function getUnitAdminsAndBosses($unitIdentifier){
        return self::where('identifier', $unitIdentifier)->with('adminsAndBosses')->first();
    }

    public static function getUnitAdmins($unitIdentifier){
        return self::where('identifier', $unitIdentifier)->with('admins')->first();
    }

    public static function getUnitBosses($unitIdentifier){
        return self::where('identifier', $unitIdentifier)->with('bosses')->first();
    }


    public static function getUnitTeachersSuitableForAssessment(){

        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod();
        $suitableTeachingLadders = $activeAssessmentPeriod->getSuitableTeachingLadders();

        $teachers = DB::table('v2_units')
            ->where('units.assessment_period_id','=', $activeAssessmentPeriod->id)
            ->join('v2_unit_user','v2_unit_user.unit_identifier','=','units.identifier')
            ->join('users','users.id','=','v2_unit_user.user_id')
            ->join('v2_teacher_profiles','v2_teacher_profiles.user_id','=','users.id')
            ->where('v2_teacher_profiles.employee_type','DTC',)
            ->whereIn('v2_teacher_profiles.teaching_ladder', $suitableTeachingLadders)->get();

    }

    public static function createOrUpdateStaffMembersUsers($staffMembers)
    {
        $now = Carbon::now()->toDateTimeString();
        $staffMemberRole = Role::getStaffMemberRoleId();
        $upsertData = [];
        $emails = [];
        foreach ($staffMembers as $staffMember) {

            $staffMemberEmail = $staffMember->mail;
            $staffMemberName = $staffMember->label;
            $staffMemberIdentification = $staffMember->value;

            $emails[] = $staffMemberEmail;
            $upsertData[] = ['email' => $staffMemberEmail,
                'name' => $staffMemberName,
                'password' => 'automatic_generate_password',
                'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($upsertData as $sqlData) {
            DB::table('users')->upsert($sqlData, 'email', null);
        }

        $users = DB::table('users')->whereIn('email', $emails)->select('id', 'email')
            ->get()->toArray();
        $roleUpsertData = [];

        foreach ($users as $user) {
            $roleUpsertData[] = ['user_id' => $user->id, 'role_id' => $staffMemberRole];
        }

        foreach ($roleUpsertData as $sqlData) {
            DB::table('role_user')->upsert($sqlData, ['user_id', 'role_id'],null);
        }

    }

    public static function getStaffMembers(){

        $staffMemberRole = Role::getStaffMemberRoleId();

        return DB::table('role_user')->where('role_id', $staffMemberRole )
            ->join('users','users.id','=', 'role_user.user_id')
            ->select('users.email','users.name','users.id')->get();
    }

    public static function getAssignedTeachers(int $assessmentPeriodId = null){

        $roleId= Role::getRoleIdByName('docente');
        if ($assessmentPeriodId === null) {
                $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return DB::table('v2_unit_user as uu')->select(['users.id','users.name'])
            ->where('role_id', $roleId)->join('users', 'uu.user_id', '=','users.id')
            ->join('v2_units as units', 'uu.unit_identifier','=', 'units.identifier')
            ->where('units.assessment_period_id', '=', $assessmentPeriodId)->orderBy('users.name', 'ASC')->get();

    }

    public static function get360GroupsResults (){
        $groupResults = DB::table('group_results as gr')
            ->select(['u.name as teacher_name', 'g.name as group_name', 'g.group as group_number','gr.competences_average',
                'gr.overall_average', 'gr.students_amount_reviewers as reviewers','gr.students_amount_on_group as total_students',
                'u.id as teacher_id','v2_unit_user.unit_identifier', 'v2_units.name as unit_name',])
            ->join('users as u', 'gr.teacher_id','=','u.id')
            ->join('groups as g', 'gr.group_id','=','g.group_id')
            ->join('v2_unit_user', 'u.id', '=', 'v2_unit_user.user_id')
            ->join('v2_units', 'v2_unit_user.unit_identifier', '=', 'v2_units.identifier')
            ->where('gr.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('v2_units.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->get();
    }

    public static function getFaculties (){
         $faculties = DB::table('unit_hierarchy')
             ->where('unit_hierarchy.assessment_period_id', '=', AssessmentPeriod::getActiveAssessmentPeriod()->id)
            ->select(['unit_hierarchy.father_unit_identifier as unit_identifier','v2_units.name'])->distinct()
             ->join('v2_units', 'unit_hierarchy.father_unit_identifier', '=', 'v2_units.identifier')->orderBy('v2_units.name', 'ASC')->get();

         return $faculties;
    }

    public static function assignStaffMemberAsUnitAdmin($unitId, $userId, $roleId){

        DB::table('v2_unit_user')
            ->updateOrInsert(['unit_identifier' => $unitId, 'role_id' => $roleId, 'user_id' => $userId]);
    }


    public static function assignTeacherAsUnitBoss($unitId, $userId, $roleId)
    {
        DB::table('v2_unit_user')
            ->updateOrInsert(['unit_identifier' => $unitId, 'role_id' => $roleId, 'user_id' => $userId]);
    }

    public static function getUnitAdmin ($unitIdentifier){

       $unitAdminRole = Role::getUnitAdminRoleId();

       return DB::table('v2_unit_user')->where('unit_identifier',$unitIdentifier)
           ->where('role_id',$unitAdminRole)
           ->join('users','users.id','=','v2_unit_user.user_id')->select('name', 'user_id')->get();
    }


    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class,'v2_unit_user','unit_identifier','user_id', 'identifier', 'id')->
        withPivot(['role_id']);
    }

    public function teachersFromUnit(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $roleId= Role::getRoleIdByName('docente');

        return $this->belongsToMany(User::class,'v2_unit_user','unit_identifier','user_id', 'identifier', 'id')->
            wherePivot('role_id', $roleId);
    }

    public function adminsAndBosses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $unitAdminRole = Role::getRoleIdByName('administrador de unidad');
        $unitBossRole= Role::getRoleIdByName('jefe de profesor');

        $rolesIds = [$unitAdminRole, $unitBossRole];

        return $this->belongsToMany(User::class,'v2_unit_user','unit_identifier','user_id', 'identifier', 'id')->
        wherePivotIn('role_id', $rolesIds)->withPivot('role_id');
    }

    public function bosses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $roleId= Role::getRoleIdByName('jefe de profesor');

        return $this->belongsToMany(User::class,'v2_unit_user','unit_identifier','user_id', 'identifier', 'id')->
        wherePivot('role_id', $roleId)->withPivot('role_id');
    }

    public function admins(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $roleId= Role::getRoleIdByName('administrador de unidad');

        return $this->belongsToMany(User::class,'v2_unit_user','unit_identifier','user_id', 'identifier', 'id')->
        wherePivot('role_id', $roleId)->withPivot('role_id');
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function teachers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherProfile::class);
    }

    public function unityAssessment(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnityAssessment::class);
    }

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }



}
