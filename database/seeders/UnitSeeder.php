<?php

namespace Database\Seeders;

use App\Http\Controllers\UnitController;
use App\Models\AssessmentPeriod;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Unit::create([
            'name' => 'Facultad de Ingeniería',
            'code' => 'FI',
            'is_custom' => false,
            'assessment_period_id' => AssessmentPeriod::getActiveAssessmentPeriod()->id
        ]);
        (new UnitController())->sync();
    }
}
