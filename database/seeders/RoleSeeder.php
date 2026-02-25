<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleCount = Role::count();

        $superadmin = Role::firstOrCreate(
            ['id' => 0],
            [
                'name' => ['de' => 'Super Admin', 'en' => 'Super Admin'],
                'weight' => 2147483646,
                'public' => false,
                'desc' => 'The Role to Role them all',
            ]
        );

        $allPermissions = collect(Permission::cases())->pluck('value');

        DB::table('role_permissions')->insertOrIgnore(
            $allPermissions->map(fn (string $permission): array => [
                'role_id' => $superadmin->id,
                'permission' => $permission,
            ])->toArray()
        );

        if ($roleCount > 0) {
            return;
        }

        Role::create(
            [
                'name' => ['de' => 'Administrator', 'en' => 'Administrator'],
                'weight' => 100,
                'public' => true,
            ]
        );

        Role::create(
            [
                'name' => ['de' => 'Community Manager', 'en' => 'Community Manager'],
                'weight' => 90,
                'public' => true,
            ]
        );
        Role::create(
            [
                'name' => ['de' => 'Moderator', 'en' => 'Moderator'],
                'weight' => 80,
                'public' => true,
            ]
        );
        Role::create(
            [
                'name' => ['de' => 'Cutter', 'en' => 'Cutter'],
                'weight' => 70,
                'public' => true,
            ]
        );
        Role::create(
            [
                'name' => ['de' => 'IT', 'en' => 'IT'],
                'weight' => 60,
                'public' => true,
            ]
        );
        Role::create(
            [
                'name' => ['de' => 'Jury', 'en' => 'Jury'],
                'weight' => 50,
                'public' => true,
            ]
        );
    }
}
