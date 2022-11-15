<?php

namespace Database\Seeders;

use App\Models\AssessmentPeriod;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Group::create([
            'name' => 'Apo 1',
            'academic_period_id' => 1,
            'class_code' => '22A01',
            'group' => '2',
            'degree' => 'pregrado',
            'service_area_id' => 1,
            'teacher_id' => 1,
            'hour_type' => 'Normal'
        ]);
        Group::create([
            'name' => 'Semiconductores',
            'academic_period_id' => 1,
            'class_code' => '22A01X',
            'group' => '2',
            'degree' => 'pregrado',
            'service_area_id' => 1,
            'teacher_id' => 1,
            'hour_type' => 'Normal'
        ]);
        Group::create([
            'name' => 'Circuitos DC',
            'academic_period_id' => 1,
            'class_code' => '24A01',
            'group' => '2',
            'degree' => 'pregrado',
            'service_area_id' => 1,
            'teacher_id' => 1,
            'hour_type' => 'Normal'
        ]);

        DB::table('group_user')
            ->insert(
                [
                    [
                        'group_id' => 1,
                        'user_id' => 1,
                        'has_answer' => 0,
                        'academic_period_id' => 1,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ],
                    [
                        'group_id' => 2,
                        'user_id' => 1,
                        'has_answer' => 0,
                        'academic_period_id' => 1,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ],
                    [
                        'group_id' => 3,
                        'user_id' => 1,
                        'has_answer' => 0,
                        'academic_period_id' => 1,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ]
                ]
            );

    }
}

