<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Test
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $degree
 * @property int|null $assessment_period_id
 * @property mixed|null $units
 * @property int|null $academic_period_id
 * @property string|null $unit_role
 * @property string|null $teaching_ladder
 * @property mixed|null $service_areas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Test newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Test newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Test query()
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereAcademicPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereAssessmentPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereServiceAreas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereTeachingLadder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereUnitRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Test extends Model
{
    protected $table = 'forms';
    use HasFactory;

    public static function getQuestionsFromTestId(int $formId)
    {
        return FormQuestion::where('form_id', $formId)->first();
    }


    public static function getUserTests($peersOrSubordinates = null, $role = null)
    {
        $user = auth()->user();
        if ($user->role()->name == "estudiante") {
            $userGroups = $user->groups;
            $userGroups = $userGroups->filter(function ($userGroup) {
                return $userGroup->teacher_id !== null;
            })->values();

            foreach ($userGroups as $group) {
                $group->test = self::getTestFromGroup($group);
            }
            return $userGroups;
        }

        if ($user->role()->name == "docente" || $user->role()->name == "jefe de profesor") {
            foreach ($peersOrSubordinates as $teacher) {
                $teacher->test = self::getTestFromTeacher($teacher, $role);
            }
            return $peersOrSubordinates;
        }
        return [];
    }

    public static function getTestFromGroup(Group $group)
    {
        $serviceAreaCode = [$group->serviceArea->code];

        // 1. Exact match on all fields
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->whereRaw("json_contains(service_areas->'$[*]', JSON_ARRAY(?))", $serviceAreaCode)
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', $group->degree)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }

        // 2. Match on degree and service_area_code, but academic_period_id is null
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->whereRaw("json_contains(service_areas->'$[*]', JSON_ARRAY(?))", $serviceAreaCode)
            ->where('academic_period_id', '=', null)
            ->where('degree', '=', $group->degree)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }

        // 3. Match on degree and academic_period_id, but service_area_code is null
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->whereRaw("json_contains(service_areas->'$[*]', JSON_ARRAY(?))", [null])
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', $group->degree)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }

        // 4. Match on service_area_code and academic_period_id, but degree is null
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->whereRaw("json_contains(service_areas->'$[*]', JSON_ARRAY(?))", $serviceAreaCode)
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', null)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }

        // 5. Match on degree only
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->where('degree', '=', $group->degree)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }

        // 6. Match on service_area_code only
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->whereRaw("json_contains(service_areas->'$[*]', JSON_ARRAY(?))", $serviceAreaCode)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }

        // 7. Match on academic_period_id only
        $form = DB::table('forms')
            ->where('type','=','estudiantes')
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        else{
            return null;
        }
    }

//    public static function getTestFromTeacherOriginal($teacher, $role)
//    {
//        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
//
//        $teachingLadders = ['ninguno' => 'NIN', 'auxiliar' => 'AUX',
//            'asistente' => 'ASI', 'asociado' => 'ASO', 'titular' => 'TIT'];
//
//        // Match teaching ladder key with the teacher's teaching ladder
//        foreach ($teachingLadders as $key => $teachingLadder) {
//            if ($teacher->teaching_ladder == $teachingLadder) {
//                $teacher->teaching_ladder = $key;
//            }
//        }
//
//        // Priority 1: Exact match on all conditions
//        $form = DB::table('forms')
//            ->where('type','=','otros')
//            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [$teacher->unit_identifier])
//            ->where('teaching_ladder', '=', $teacher->teaching_ladder)
//            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
//            ->where('unit_role', '=', $role)
//            ->latest()->first();
//
//        if ($form !== null) {
//            return $form;
//        }
//
//        // Priority 2: Match on teaching_ladder, unit_role, and assessment_period_id (unit is null)
//        $form = Form::whereJsonContains('units', null)
//            ->where('teaching_ladder', '=', $teacher->teaching_ladder)
//            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
//            ->where('unit_role', '=', $role)
//            ->latest()->first();
//
//        if ($form !== null) {
//            return $form;
//        }
//
//        // Priority 3: Match on unit_role and assessment_period_id (teaching_ladder and unit are null)
//        $form = Form::whereJsonContains('units', null)
//            ->where('teaching_ladder', '=', null)
//            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
//            ->where('unit_role', '=', $role)
//            ->latest()->first();
//
//        if ($form !== null) {
//            return $form;
//        }
//
//        // Priority 4: Match on assessment_period_id only (teaching_ladder, unit_role, and unit are null)
//        $form = DB::table('forms')
//            ->where('type','=','otros')
//            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [null])
//            ->where('teaching_ladder', '=', null)
//            ->where('assessment_period_id', '=', null)
//            ->where('unit_role', '=', $role)
//            ->latest()->first();
//
//        return $form ?? null;
//    }

    public static function getTestFromTeacher($teacher, $role)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $teachingLadders = [
            'ninguno' => 'NIN',
            'auxiliar' => 'AUX',
            'asistente' => 'ASI',
            'asociado' => 'ASO',
            'titular' => 'TIT'
        ];

        // Match teaching ladder key with the teacher's teaching ladder
        foreach ($teachingLadders as $key => $teachingLadder) {
            if ($teacher->teaching_ladder == $teachingLadder) {
                $teacher->teaching_ladder = $key;
            }
        }

        // Priority 1: Exact match on all conditions
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [$teacher->unit_identifier])
            ->where('teaching_ladder', '=', $teacher->teaching_ladder)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        // Priority 2: Match on teaching_ladder, unit_role, and assessment_period_id (unit is null)
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [$teacher->unit_identifier])
            ->where('teaching_ladder', '=', null)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        // Priority 3: Match on unit_role and assessment_period_id (teaching_ladder and unit are null)
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [$teacher->unit_identifier])
            ->where('teaching_ladder', '=', null)
            ->where('assessment_period_id', '=', null)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        // Priority 4: Match on teaching_ladder and assessment_period_id (unit_role and unit are null)
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [null])
            ->where('teaching_ladder', '=', $teacher->teaching_ladder)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }


        // Priority 5: Match on unit_role only (teaching_ladder, assessment_period_id, and unit are null)
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [null])
            ->where('teaching_ladder', '=', $teacher->teaching_ladder)
            ->where('assessment_period_id', '=', null)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        // Priority 6: Match on unit_role only (teaching_ladder, assessment_period_id, and unit are null)
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [$teacher->unit_identifier])
            ->where('teaching_ladder', '=', null)
            ->where('assessment_period_id', '=',  $activeAssessmentPeriodId)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [null])
            ->where('teaching_ladder', '=', null)
            ->where('assessment_period_id', '=',  $activeAssessmentPeriodId)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }


        // Priority 8: Match on teaching_ladder only (unit_role, assessment_period_id, and unit are null)
        $form = DB::table('forms')
            ->where('type', '=', 'otros')
            ->whereRaw("json_contains(units->'$[*]', JSON_ARRAY(?))", [null])
            ->where('teaching_ladder', '=', null)
            ->where('assessment_period_id', '=', null)
            ->where('unit_role', '=', $role)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        else{
            return null;
        }
    }
}
