<?php

namespace Database\Seeders;

use App\Models\Clip\Tag;
use App\Models\User;
use App\Enums\Permission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //User::firstOrCreate(
        //    ['email' => 'test@example.com'],
        //    [
        //        'name' => 'Test User',
        //        'password' => 'password',
        //        'email_verified_at' => now(),
        //    ]
        //);

        $this->call([
            RoleSeeder::class
        ]);

        // Kindly wipe unused permission pivots on deployment
        DB::table('role_permissions')->whereNotIn('permission', Permission::cases())->delete();
    }
}
