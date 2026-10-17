<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'id' => 0,
        ], [
            'name' => 'System',
            'avatar_url' => 'https://api.dicebear.com/9.x/pixel-art/svg?seed='.Str::random(),
            'created_at' => Carbon::parse('1970-01-01 00:00:00'),
        ]);

        // System/Placeholder Category that twitch may return in some cases
        Category::updateOrCreate([
            'id' => 0,
        ], [
            'title' => 'Unbekannt',
            'box_art' => Category::PLACEHOLDER_BOX_ART,
            'is_banned' => false,
            'created_at' => Carbon::parse('1970-01-01 00:00:00'),
        ]);

        // Kindly wipe unused permission pivots on deployment
        DB::table('role_permissions')->whereNotIn('permission', Permission::cases())->delete();

        $this->call([
            RoleSeeder::class,
            TeamSeeder::class,
            TagSeeder::class,
            InitialEpisodeSeeder::class,
            FaqSeeder::class,
            FeatureFlagSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
