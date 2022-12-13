<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Initial Setup
        Role::create([
            'name' => 'administrador',
            'customId' => 10
        ]);

        Role::create([
            'name' => 'administrador de unidad',
            'customId' => 5
        ]);

        Role::create([
            'name' => 'jefe de profesor',
            'customId' => 4
        ]);

        Role::create([
            'name' => 'par',
            'customId' => 3
        ]);
        Role::create([
            'name' => 'docente',
            'customId' => 2
        ]);

        Role::create([
            'name' => 'estudiante',
            'customId' => 1
        ]);
    }
}
