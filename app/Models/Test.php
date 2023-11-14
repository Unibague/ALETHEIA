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
        if($user->role()->name == "estudiante"){
            $userGroups = $user->groups;
            $userGroups = $userGroups->filter(function($userGroup){
                return $userGroup->teacher_id !== null;
            })->values();

            foreach ($userGroups as $group) {
                $group->test = self::getTestFromGroup($group);
            }
            return $userGroups;
        }

        if ($user->role()->name == "docente" || $user->role()->name == "jefe de profesor"){
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
        //All params
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*]',JSON_ARRAY(?))", $serviceAreaCode)
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', $group->degree)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }
        //Only last two params
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*]',JSON_ARRAY(?))",[null])
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', $group->degree)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Only first param
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*]',JSON_ARRAY(?))",[null])
            ->where('academic_period_id', '=', null)
            ->where('degree', '=', $group->degree)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Any params
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*]',JSON_ARRAY(?))",[null])
            ->where('academic_period_id', '=', null)
            ->where('degree', '=', null)
            ->latest()->first();

        return $form ?? null;
    }

    public static function getTestFromTeacher($teacher, $role)
    {

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $teachingLadders = ['ninguno' => 'NIN', 'auxiliar' => 'AUX',
            'asistente' => 'ASI', 'asociado' => 'ASO', 'titular' => 'TIT'];

        foreach ($teachingLadders as $key => $teachingLadder) {
            if ($teacher->teaching_ladder == $teachingLadder) {
                $teacher->teaching_ladder = $key;
            }
        }

        $form = Form::whereJsonContains('units', [$teacher->unit_identifier])
                ->where('teaching_ladder', '=', $teacher->teaching_ladder)
                ->where('unit_role', '=', $role)
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Only last two params
        $form = Form::whereJsonContains('units', null)
            ->where('teaching_ladder', '=', $teacher->teaching_ladder)
            ->where('unit_role', '=', $role)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Only first param
        $form = Form::whereJsonContains('units', null)
            ->where('teaching_ladder', '=',null)
            ->where('unit_role', '=', $role)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Any params
        $form = Form::whereJsonContains('units', null)
            ->where('teaching_ladder', '=',null)
            ->where('unit_role', '=', null)
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }

        $form = Form::whereJsonContains('units', null)
            ->where('teaching_ladder', '=',null)
            ->where('unit_role', '=', null)
            ->where('assessment_period_id', '=', null)
            ->latest()->first();

        return $form ?? null;
    }


}
