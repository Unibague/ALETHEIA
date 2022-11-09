<?php

namespace Database\Seeders;

use App\Http\Controllers\ServiceAreaController;
use App\Models\ServiceArea;
use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceArea::create([
            'name' => 'Programa Ingenieria Electronica',
            'code' => '24',
            'assessment_period_id' => 1
        ]);

        (new ServiceAreaController())->sync();

    }
}
