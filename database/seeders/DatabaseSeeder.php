<?php

namespace Database\Seeders;

use App\Models\Role;
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
        Role::create([
            'name' => 'administrador',
            'customId' => 10
        ]);

        Role::create([
            'name' => 'estudiante',
            'customId' => 1
        ]);
    }
}
