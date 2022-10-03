<?php

namespace Database\Seeders;

use App\Models\AssessmentPeriod;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'administrador',
            'customId' => 10
        ]);

        Role::create([
            'name' => 'estudiante',
            'customId' => 1
        ]);

        AssessmentPeriod::create([
            'name' => 'Periodo 1',
            'self_start_date' => Carbon::now()->toDateTimeString(),
            'self_end_date' => Carbon::now()->toDateTimeString(),
            'boss_start_date' => Carbon::now()->toDateTimeString(),
            'boss_end_date' => Carbon::now()->toDateTimeString(),
            'colleague_start_date' => Carbon::now()->toDateTimeString(),
            'colleague_end_date' => Carbon::now()->toDateTimeString(),
            'active' => 1,
            'done_by_none' => 0,
            'done_by_auxiliary' => 0,
            'done_by_assistant' => 0,
            'done_by_associated' => 0,
            'done_by_head_teacher' => 0,
        ]);

        (new UnitySeeder)->run();
    }
}
