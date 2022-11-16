<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Form::create([
            'name' => 'Formulario general',
            'type' => 'estudiantes',
            'degree' => null,
            'academic_period_id' => null,
            'service_areas' => '[{"id": null, "name": "Todas"}]'
            ,]);

    }
}
