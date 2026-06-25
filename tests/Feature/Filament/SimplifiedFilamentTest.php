<?php

declare(strict_types=1);

use App\Enums\FeatureFlag;
use App\Enums\Permission;
use App\Filament\AdminPanel\Pages\FeatureFlags;
use App\Filament\AdminPanel\Resources\Audits\Pages\ListAudits;
use App\Filament\AdminPanel\Resources\Bans\Pages\ListBans;
use App\Filament\AdminPanel\Resources\Broadcasters\Pages\ListBroadcasters;
use App\Filament\AdminPanel\Resources\Categories\Pages\ListCategories;
use App\Filament\AdminPanel\Resources\Clips\Pages\ListClips as AdminListClips;
use App\Filament\AdminPanel\Resources\Compilations\Pages\ListCompilations;
use App\Filament\AdminPanel\Resources\FaqEntries\Pages\ListFaqEntries;
use App\Filament\AdminPanel\Resources\RemovalRequests\Pages\ListRemovalRequests;
use App\Filament\AdminPanel\Resources\Reports\Pages\ListReports;
use App\Filament\AdminPanel\Resources\Roles\Pages\ListRoles;
use App\Filament\AdminPanel\Resources\ShortUrls\Pages\ManageShortUrls;
use App\Filament\AdminPanel\Resources\Tags\Pages\ListTags;
use App\Filament\AdminPanel\Resources\Users\Pages\ListUsers;
use App\Filament\Dashboard\Pages\Broadcaster\GeneralSettings;
use App\Filament\Dashboard\Pages\Broadcaster\ManageCategoryFilter;
use App\Filament\Dashboard\Pages\Broadcaster\ManageTeamMember;
use App\Filament\Dashboard\Pages\Broadcaster\ManageUserFilter;
use App\Filament\Dashboard\Pages\Dashboard as BroadcasterDashboard;
use App\Filament\Dashboard\Resources\Clips\Pages\ListClips;
use App\Filament\Dashboard\Resources\RemovalRequests\Pages\ListRemovalRequests as BroadcasterListRemovalRequests;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Support\FeatureFlag\Feature;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as AdminDashboard;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    Role::factory()->create(['id' => 0]);
    $user = User::factory()->create(['id' => 0]);
    $user->syncRoles([0]);

    actingAs($user);

    Http::preventStrayRequests();
});

describe('Dashboard', function () {
    beforeEach(function () {
        $user = User::factory()->create(['id' => 1, 'twitch_refresh_token' => 'cake']);
        $broadcaster = $user->broadcaster()->create();

        actingAs($user);

        Filament::setCurrentPanel('dashboard');
        Filament::setTenant($broadcaster);
        Filament::bootCurrentPanel();
    });

    describe('Own', function () {
        beforeEach(function () {
            Http::fake();
        });
        afterEach(function () {
            Http::assertNothingSent();
        });

        it('can load the dashboard', function () {
            livewire(BroadcasterDashboard::class)
                ->assertOk();
        });

        it('can access list page', function (string $page) {
            livewire($page)
                ->assertOk();
        })->with([
            'Clips' => ListClips::class,
            'Removal Requests' => BroadcasterListRemovalRequests::class,
        ]);

        describe('Settings', function () {
            it('can access general settings', function () {
                livewire(GeneralSettings::class)
                    ->assertOk();
            });

            it('can access team member settings', function () {
                livewire(ManageTeamMember::class)
                    ->assertOk();
            })->skip(fn () => ! Feature::isActive(FeatureFlag::BroadcasterTenant), 'feature flag disabled');

            it('can access category filter settings', function () {
                livewire(ManageCategoryFilter::class)
                    ->assertOk();
            });

            it('can access user filter settings', function () {
                livewire(ManageUserFilter::class)
                    ->assertOk();
            })->skip(fn () => ! Feature::isActive(FeatureFlag::BroadcasterUserSubmissionFilterManager), 'feature flag disabled');
        });
    });

    describe('Other', function () {
        beforeEach(function () {
            $otherUSer = User::factory()->create(['id' => 2]);
            $broadcaster = $otherUSer->broadcaster()->create();

            Filament::setTenant($broadcaster);
            Filament::bootCurrentPanel();
        });

        it('can not access others without permission', function () {
            Http::fake([
                'https://id.twitch.tv/oauth2/token' => Http::response([
                    'access_token' => 'fake-app-access-token',
                    'expires_in' => 5011271,
                    'token_type' => 'bearer',
                ]),
                'https://api.twitch.tv/helix/moderation/channels*' => Http::response([
                    'data' => [],
                ]),
            ]);

            livewire(BroadcasterDashboard::class)
                ->assertForbidden();

            livewire(ListClips::class)
                ->assertForbidden();

            livewire(BroadcasterListRemovalRequests::class)
                ->assertForbidden();

            livewire(GeneralSettings::class)
                ->assertForbidden();

            if (Feature::isActive(FeatureFlag::BroadcasterTenant)) {
                livewire(ManageTeamMember::class)
                    ->assertForbidden();
            }

            livewire(ManageCategoryFilter::class)
                ->assertForbidden();

            if (Feature::isActive(FeatureFlag::BroadcasterUserSubmissionFilterManager)) {
                livewire(ManageUserFilter::class)
                    ->assertForbidden();
            }

            Http::assertSentCount(2);
        });
    });
});

describe('Admin', function () {
    $resourceLists = [
        'Clips' => [AdminListClips::class, Permission::ViewAnyClip],
        'Compilations' => [ListCompilations::class, Permission::ViewAnyCompilation],
        'Broadcasters' => [ListBroadcasters::class, Permission::ViewAnyBroadcaster],
        'Audits' => [ListAudits::class, Permission::ViewAnyAudit],
        'Roles' => [ListRoles::class, Permission::ViewAnyRole],
        'Users' => [ListUsers::class, Permission::ViewAnyUser],
        'Bans' => [ListBans::class, Permission::ViewAnyBan],
        'Removal Requests' => [ListRemovalRequests::class, Permission::ViewAnyRemovalRequest],
        'Reports' => [ListReports::class, Permission::ViewAnyReport],
        'FAQ Entries' => [ListFaqEntries::class, Permission::ViewAnyFaqEntry],
        'Short URLs' => [ManageShortUrls::class, Permission::ViewAnyShortUrl],
        'Categories' => [ListCategories::class, Permission::ViewAnyCategory],
        'Tags' => [ListTags::class, Permission::ViewAnyTag],
    ];

    describe('SuperAdmin', function () use ($resourceLists) {
        it('can access the dashboard', function () {
            livewire(AdminDashboard::class)
                ->assertOk();
        });

        it('can access feature flags', function () {
            livewire(FeatureFlags::class)
                ->assertOk();
        });

        it('can access list page', function (string $page, Permission $permission) {
            livewire($page)
                ->assertOk();
        })->with($resourceLists);
    });

    describe('Users with specific permissions', function () use ($resourceLists) {
        it('can access list page', function (string $page, Permission $permission) {
            $user = User::factory()->create();
            actingAs($user);

            // make sure we properly get 4xx'ed
            get('/admin')
                ->assertForbidden();
            livewire($page)
                ->assertForbidden();

            // then grant the permission
            $role = $user->roles()
                ->create(['name' => $permission->name]);
            RolePermission::create([
                'permission' => $permission->value,
                'role_id' => $role->id,
            ]);
            $user->refresh();
            actingAs($user);

            // then make sure we dont get 4xx'ed
            get('/admin')
                ->assertOk();
            livewire($page)
                ->assertOk();
        })->with($resourceLists);
    });

    describe('Normal Users', function () use ($resourceLists) {
        beforeEach(function () {
            $user = User::factory()->create();
            actingAs($user);
        });

        it('can not access the dashboard', function () {
            get('/admin')
                ->assertForbidden();
        });

        it('can not access feature flags', function () {
            livewire(FeatureFlags::class)
                ->assertForbidden();
        });

        it('can not access list page', function (string $page) {
            livewire($page)
                ->assertForbidden();
        })->with($resourceLists);
    });
});
