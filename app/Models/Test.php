<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Test extends Model
{
    use HasFactory;

    public static function getUserTests()
    {
        $user = auth()->user();
        $userGroups = $user->groups;

        foreach ($userGroups as $key => $group) {
            $group->test = self::getTestFromGroup($group);
        }
    }

    public static function getTestFromGroup(Group $group)
    {
        Form::where('service_areas_id')

        return (object)[
            'name' => 'Test hola'
        ];
    }
}
