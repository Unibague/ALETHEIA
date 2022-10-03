<?php

namespace Database\Seeders;

use App\Models\AssessmentPeriod;
use App\Models\Unity;
use Illuminate\Database\Seeder;

class UnitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Unity::create([
            'name' => 'Facultad de Ingeniería',
            'code' => 'FI',
            'is_custom' => false,
            'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
        ]);
    }
}
