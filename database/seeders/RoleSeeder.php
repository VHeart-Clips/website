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
                'name' => 'Administrator'
            ],
            [
                'weight' => 100,
                'public' => true
            ]
        );
        Role::firstOrCreate(
            [
                'name' => 'Community Manager'
            ],
            [
                'weight' => 90,
                'public' => true
            ]
        );
        Role::firstOrCreate(
            [
                'name' => 'Moderator'
            ],
            [
                'weight' => 80,
                'public' => true
            ]
        );
        Role::firstOrCreate(
            [
                'name' => 'Cutter'
            ],
            [
                'weight' => 70,
                'public' => true
            ]
        );
        Role::firstOrCreate(
            [
                'name' => 'IT'
            ],
            [
                'weight' => 60,
                'public' => true
            ]
        );
        Role::firstOrCreate(
            [
                'name' => 'Jury'
            ],
            [
                'weight' => 50,
                'public' => true
            ]
        );
    }
}
