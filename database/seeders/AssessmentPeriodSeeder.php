<?php

namespace Database\Seeders;

use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssessmentPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        AssessmentPeriod::create([
            'name' => '2023-A',
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
        AssessmentPeriod::create([
            'name' => '2023-B',
            'self_start_date' => Carbon::now()->toDateTimeString(),
            'self_end_date' => Carbon::now()->toDateTimeString(),
            'boss_start_date' => Carbon::now()->toDateTimeString(),
            'boss_end_date' => Carbon::now()->toDateTimeString(),
            'colleague_start_date' => Carbon::now()->toDateTimeString(),
            'colleague_end_date' => Carbon::now()->toDateTimeString(),
            'active' => 0,
            'done_by_none' => 1,
            'done_by_auxiliary' => 1,
            'done_by_assistant' => 1,
            'done_by_associated' => 1,
            'done_by_head_teacher' => 1,
        ]);

    }
}
