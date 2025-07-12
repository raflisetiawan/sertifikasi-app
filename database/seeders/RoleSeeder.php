<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'name' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add user role
        DB::table('roles')->insert([
            'name' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add trainer role
        DB::table('roles')->insert([
            'name' => 'trainer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
