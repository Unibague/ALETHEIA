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
            'name' => 'Formulario facultad ciencias naturales y matematicas ',
            'type' => 'estudiantes',
            'degree' => 'Pregrado',
            'academic_period_id' => 1,
            'service_areas' => '[{"id": null, "name": "Todas"}]',
        ]);
    }
}
