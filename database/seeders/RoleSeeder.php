<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            [
                'name' => 'Admin'
            ],
            [
                'desc' => 'Projekt Admins',
                'weight' => 100
            ]
        );       
        Role::firstOrCreate(
            [
                'name' => 'Mod'
            ],
            [
                'desc' => 'Projekt Mods',
                'weight' => 80
            ]
        );
    }
}
