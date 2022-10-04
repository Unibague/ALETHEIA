<?php

namespace Database\Seeders;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user = User::create([
            'name' => 'Tatiana Cifuentes',
            'email' => 'tatiana.cifuentes@unibague.edu.co',
            'password' => '12345',
        ]);

        TeacherProfile::create([
            'assessment_period_id' => 1,
            'identification_number' => '1110590915',
            'user_id' => $user->id,
            'unity' => 'Facultad ciencias naturales y matematicas',
            'position' => 'Profesor Tiempo Completo',
            'teaching_ladder' => 'Ninguno',
            'employee_type' => 'DTC',
            'status' => 'Activo',]);

        $user2 = User::create([
            'name' => 'Luz Esther Gonzales',
            'email' => 'luz.gonzalez@unibague.edu.co',
            'password' => '123456',]);
        TeacherProfile::create([
            'assessment_period_id' => 2,
            'identification_number' => '1110590914',
            'user_id' => $user2->id,
            'unity' => 'Facultad ciencias naturales y matematicas',
            'position' => 'Profesor Tiempo Completo',
            'teaching_ladder' => 'Ninguno',
            'employee_type' => 'DTC',
            'status' => 'Suspendido',]);

    }
}
