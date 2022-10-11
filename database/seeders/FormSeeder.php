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
            'type' => 'Estudiante',
            'degree'=> 'Pregrado',
            'academic_period_id' => 1,
            'service_area_id'=> 1,
        ]);
    Form::create([
       'name'=>'Formulario facultad ingenieria',
        'type'=>'Otros',
        'assessment_period_id'=> 1,
        'unit_role'=>'Jefe',
        'teaching_ladder'=>'Titular',
        'unity_id'=> 1

    ]);
    }
}
