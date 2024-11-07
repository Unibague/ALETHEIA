<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ServiceArea
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $assessment_period_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentPeriod $assessmentPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Form[] $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @method static \Database\Factories\ServiceAreaFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceArea whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceArea extends Model
{

    protected $guarded = [];
    use HasFactory;

    public static function getCurrentServiceAreas(int $assessmentPeriodId = null){
        if ($assessmentPeriodId === null){
            $assessmentPeriodId =AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
        return self::where('assessment_period_id','=', $assessmentPeriodId)->orderBy('name')->get();
    }

    public static function createOrUpdateFromArray(array $serviceAreas): void
    {
        $upsertData = [];
        $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        foreach ($serviceAreas as $serviceArea) {
            $upsertData[] = [
                'code' => $serviceArea->code,
                'name' => $serviceArea->name,
                'assessment_period_id' => $assessmentPeriodId,
            ];
        }
        self::upsert($upsertData, ['code', 'assessment_period_id'], ['name', 'assessment_period_id','updated_at']);
    }

    public static function getUsersAssignedToServiceAreas()
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $serviceAreasGrouped = DB::table('service_area_user')
            ->join('service_areas', 'service_area_user.service_area_code', '=', 'service_areas.code')
            ->join('users', 'service_area_user.user_id', '=', 'users.id') // Join with the users table
            ->select(
                'service_area_user.user_id',
                'users.name as user_name',
                'users.email as user_email',
                'service_area_user.service_area_code',
                'service_areas.name as service_area_name'
            )
            ->where('service_area_user.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('service_areas.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->orderBy('users.name',)
            ->get()
            ->groupBy('user_id');

        // Transform the grouped data into the desired structure
        $result = $serviceAreasGrouped->map(function ($serviceAreas, $userId) {
            return [
                'user' => [
                    'id' => $userId,
                    'name' => $serviceAreas[0]->user_name, // Assuming all entries for a user have the same name
                    'email' => $serviceAreas[0]->user_email // Assuming all entries for a user have the same email
                ],
                'service_areas' => $serviceAreas->map(function ($area) {
                    return [
                        'service_area_code' => $area->service_area_code,
                        'service_area_name' => $area->service_area_name,
                    ];
                })->values()->all() // Convert to array and reset keys
            ];
        })->values()->all(); // Convert to array and reset keys at the outer level

        return $result; // Return the structured result
    }


    public static function getServiceAreasResults(int $assessmentPeriodId = null)
    {

        if ($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return  DB::table('teachers_service_areas_results as tsar')
            ->select([ 't.name', 'sa.name as service_area_name' , 'tsar.service_area_code', 't.id as teacherId',
                'tsar.first_final_aggregate_competence_average as first_competence_average',
            'tsar.second_final_aggregate_competence_average as second_competence_average',
            'tsar.third_final_aggregate_competence_average as third_competence_average',
            'tsar.fourth_final_aggregate_competence_average as fourth_competence_average',
            'tsar.fifth_final_aggregate_competence_average as fifth_competence_average',
            'tsar.sixth_final_aggregate_competence_average as sixth_competence_average', 'tsar.updated_at as submitted_at',
                'tsar.aggregate_students_amount_reviewers', 'tsar.aggregate_students_amount_on_service_area'])
            ->join('users as t', 'tsar.teacher_id', '=', 't.id')
            ->join('service_areas as sa', 'sa.code','=','tsar.service_area_code')
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->where('tsar.assessment_period_id', '=', $assessmentPeriodId)->orderBy('name', 'ASC')
            ->get();
    }

    public static function getServiceAreasResultsPerGroup(int $assessmentPeriodId = null)
    {

        if ($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return DB::table('group_results as gr')->where('gr.assessment_period_id', '=', $assessmentPeriodId)
            ->select([ 't.name', 'sa.name as service_area_name' , 'gr.service_area_code', 't.id as teacherId', 'gr.group_id',
                'g.name as group_name', 'g.group as group_number', 'ap.name as academic_period_name',
                'gr.first_final_competence_average as first_competence_average',
                'gr.second_final_competence_average as second_competence_average',
                'gr.third_final_competence_average as third_competence_average',
                'gr.fourth_final_competence_average as fourth_competence_average',
                'gr.fifth_final_competence_average as fifth_competence_average',
                'gr.sixth_final_competence_average as sixth_competence_average', 'gr.updated_at as submitted_at',
                'gr.students_amount_reviewers', 'gr.students_amount_on_group'])
            ->join('groups as g', 'gr.group_id', '=', 'g.group_id')
            ->join('users as t', 'gr.teacher_id', '=', 't.id')
            ->join('service_areas as sa', 'sa.code','=','gr.service_area_code')
            ->where('sa.assessment_period_id', '=', $assessmentPeriodId)
            ->join('academic_periods as ap', 'ap.id', '=', 'g.academic_period_id')->get();
    }


    public static function getServiceAreasTeachers(int $assessmentPeriodId = null)
    {
        if ($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
        $teachers = DB::table('teachers_service_areas_results as tsar')
            ->select(['t.name','t.id as id'])
            ->join('users as t', 'tsar.teacher_id', '=', 't.id')
            ->where('tsar.assessment_period_id', '=', $assessmentPeriodId)->orderBy('name', 'ASC')
            ->get()->unique();

        return $teachers->values()->toArray();

    }

    public static function getServiceAreasTeachersWithResults(int $assessmentPeriodId = null)
    {
        if ($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }
        return  DB::table('teachers_service_areas_results as tsar')
            ->select(['t.name', 'sa.name as service_area_name' , 'tsar.service_area_code', 't.id as id'])
            ->join('users as t', 'tsar.teacher_id', '=', 't.id')
            ->join('service_areas as sa', 'sa.code','=','tsar.service_area_code')
            ->where('tsar.assessment_period_id', '=', $assessmentPeriodId)->orderBy('name', 'ASC')
            ->get();
    }

    public static function getServiceAreaAdmins($serviceArea)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        return DB::table('service_area_user as sau')
            ->select(['t.name', 'sau.user_id as teacherId' , 'sau.service_area_code'])
            ->join('users as t', 'sau.user_id', '=', 't.id')
            ->join('service_areas as sa', 'sa.code','=','sau.service_area_code')
            ->where('sau.service_area_code', '=', $serviceArea)
            ->where('sa.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('sau.assessment_period_id', '=', $activeAssessmentPeriodId)->orderBy('name', 'ASC')
            ->get();

    }


    public static function assignServiceAreaAdmin($serviceArea, $userId)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $serviceAreaAdminRole = Role::getServiceAreaAdminRoleId();

        $assignment = DB::table('service_area_user')->where('service_area_code', '=', $serviceArea)
            ->where('role_id', '=', $serviceAreaAdminRole)->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('user_id', '=', $userId)->first();

        if ($assignment !== null){

            return response()->json(['message' => 'Esa persona ya es administradora de esta unidad'],500);

        }

        DB::table('service_area_user')->updateOrInsert(['user_id' => $userId, 'service_area_code' => $serviceArea],
            ['role_id' => $serviceAreaAdminRole,
            'assessment_period_id' =>$activeAssessmentPeriodId ]);

        return response()->json(['message' => 'Adminstrador de unidad actualizado exitosamente']);
    }



    public static function deleteServiceAreaAdmin($serviceArea, $userId)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $serviceAreaAdminRole = Role::getServiceAreaAdminRoleId();

        DB::table('service_area_user')->where('service_area_code', '=', $serviceArea)
            ->where('role_id', '=', $serviceAreaAdminRole)->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('user_id', '=', $userId)->delete();

        return response()->json(['message' => 'Adminstrador de unidad eliminado exitosamente']);
    }




    public function groups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        return $this->belongsToMany(User::class, 'service_area_user', 'service_area_code', 'user_id', 'code', 'id')
            ->where('service_area_user.assessment_period_id', '=', $activeAssessmentPeriodId);
    }

    public function faculties()
    {
        return $this->belongsToMany(Faculty::class, 'faculty_service_area', 'service_area_code', 'faculty_id');
    }

}
