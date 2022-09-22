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
            'name' => 'admin',
            'customId' => 10
        ]);

        Role::create([
            'name' => 'student',
            'customId' => 1
        ]);
    }
}
