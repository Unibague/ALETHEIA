<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Test extends Model
{
    protected $table = 'forms';
    use HasFactory;

    public static function getQuestionsFromTestId(int $formId)
    {
        return FormQuestion::where('form_id', $formId)->first();
    }

    public static function getUserTests()
    {
        $user = auth()->user();
        $userGroups = $user->groups;
        foreach ($userGroups as $group) {
            $group->test = self::getTestFromGroup($group);
        }
        return $userGroups;
    }

    public static function getTestFromGroup(Group $group)
    {
        $serviceAreaId = $group->serviceArea->id;

        //All params
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*].id',JSON_ARRAY($serviceAreaId))")
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', $group->degree)
            ->latest()->first();
        if ($form !== null) {
            return $form;
        }
        //Only last two params
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*].id',JSON_ARRAY(null))")
            ->where('academic_period_id', '=', $group->academic_period_id)
            ->where('degree', '=', $group->degree)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Only first param
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*].id',JSON_ARRAY(null))")
            ->where('academic_period_id', '=', null)
            ->where('degree', '=', $group->degree)
            ->latest()->first();

        if ($form !== null) {
            return $form;
        }
        //Any params
        $form = DB::table('forms')
            ->whereRaw("json_contains(service_areas->'$[*].id',JSON_ARRAY(null))")
            ->where('academic_period_id', '=', null)
            ->where('degree', '=', null)
            ->latest()->first();

        return $form ?? null;
    }
}
