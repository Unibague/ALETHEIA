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

        (new RoleSeeder())->run();

        //Create admin user
        $user = User::create([
            'name' => 'Sebastian Ospina',
            'email' => 'juan.ospina@unibague.edu.co',
            'password' => '12345',
        ]);

        //Assign role to the user

        $user->roles()->attach(1);

        (new AssessmentPeriodSeeder)->run();
        (new AcademicPeriodSeeder)->run('2022B');
        (new UnitSeeder)->run();
        (new ServiceAreaSeeder)->run();
        (new TeacherProfileSeeder)->run();
        (new GroupSeeder)->run();
        (new FormSeeder)->run();
        (new FormQuestionSeeder)->run();

    }
}
