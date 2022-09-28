<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use Illuminate\Database\Seeder;

class AcademicPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($name): void
    {
        AcademicPeriod::create([
            'name' => $name,
            'class_start_date' => '2022-02-01',
            'class_end_date' => '2022-02-01',
        ]);
    }
}
