<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

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
            'name' => 'apo1',
            'academic_period_id' => 1,
            'class_code' => '22A01',
            'group' => '2',
            'degree' => 'pregrado',
            'service_area_id' => 1,
            'teacher_id' => 1,
            'hour_type' => 'Normal'

        ]);
    }
}

