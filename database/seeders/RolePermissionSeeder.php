<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RolePermissionSeeder extends Seeder
{
    protected array $adminPermissionsBlacklist = [
        Permission::ForceDeleteAnyCompilation,
        Permission::ForceDeleteAnyFaqEntry,
        Permission::DeleteAnyComment,
        Permission::ForceDeleteAnyClip,
        Permission::ForceDeleteAnyBroadcaster,
        Permission::DeleteAnyRemovalRequest,
        Permission::BypassConsentCheck,
    ];

    protected array $communityManagerPermissions = [
        Permission::JuryVote,
    ];

    protected array $moderatorPermissions = [
        Permission::ViewAnyUser,
        Permission::UpdateAnyUser,
        Permission::DeleteAnyUser,
        Permission::RestoreAnyUser,
        Permission::ViewAnyReport,
        Permission::UpdateAnyReport,
        Permission::DeleteAnyReport,
        Permission::RestoreAnyReport,
        Permission::ViewAnyCompilation,
        Permission::ViewAnyCategory,
        Permission::UpdateAnyCategory,
        Permission::ViewAnyFaqEntry,
        Permission::ViewAnyComment,
        Permission::CreateComment,
        Permission::ViewAnyTag,
        Permission::ViewAnyClip,
        Permission::CreateClip,
        Permission::UpdateAnyClip,
        Permission::DeleteAnyClip,
        Permission::RestoreAnyClip,
        Permission::ViewAnyBroadcaster,
        Permission::DeleteAnyBroadcaster,
        Permission::RestoreAnyBroadcaster,
        Permission::ViewAnyBroadcasterSubmissionFilter,
        Permission::ViewAnyBroadcasterTeamMember,
        Permission::JuryVote,
        Permission::BypassMaximumAgeLimitCheck,
        Permission::BypassMinimumLengthRequirementCheck,
        Permission::BypassBannedCategoryCheck,
        Permission::CanFlagClips,
    ];

    protected array $cutterPermissions = [
        Permission::ViewAnyUser,
        Permission::ViewAnyCompilation,
        Permission::CreateCompilation,
        Permission::ViewAnyCategory,
        Permission::ViewAnyComment,
        Permission::CreateComment,
        Permission::ViewAnyTag,
        Permission::ViewAnyClip,
        Permission::CreateClip,
        Permission::UpdateAnyClip,
        Permission::ViewAnyBroadcaster,
        Permission::JuryVote,
        Permission::CanSubmitClipFeedback,
        Permission::CanFlagClips,
    ];

    protected array $itPermissions = [
        Permission::ViewAnyCompilation,
        Permission::CreateCompilation,
        Permission::UpdateAnyCompilation,
        Permission::DeleteAnyCompilation,
        Permission::RestoreAnyCompilation,
        Permission::ViewAnyCategory,
        Permission::CreateCategory,
        Permission::UpdateAnyCategory,
        Permission::ViewAnyFaqEntry,
        Permission::CreateFaqEntry,
        Permission::UpdateAnyFaqEntry,
        Permission::DeleteAnyFaqEntry,
        Permission::RestoreAnyFaqEntry,
        Permission::ViewAnyComment,
        Permission::CreateComment,
        Permission::DeleteAnyComment,
        Permission::ViewAnyTag,
        Permission::CreateTag,
        Permission::UpdateAnyTag,
        Permission::ViewAnyClip,
        Permission::CreateClip,
        Permission::UpdateAnyClip,
        Permission::DeleteAnyClip,
        Permission::RestoreAnyClip,
        Permission::ViewAnyBroadcaster,
        Permission::ViewAnyBroadcasterConsentLog,
        Permission::ViewAnyBroadcasterSubmissionFilter,
        Permission::ViewAnyBroadcasterTeamMember,
        Permission::ViewAnyAudit,
        Permission::JuryVote,
        Permission::CanFlagClips,
    ];

    protected array $juryPermissions = [
        Permission::JuryVote,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (RolePermission::count() > 0) {
            return;
        }

        $adminPermissions = array_filter(Permission::cases(), fn (Permission $value): bool => ! in_array($value, $this->adminPermissionsBlacklist));

        $permissionMapping = [
            1 => $adminPermissions, // Administrator
            2 => $this->communityManagerPermissions, // Community Manager
            3 => $this->moderatorPermissions, // Moderator
            4 => $this->cutterPermissions, // Cutter
            5 => $this->itPermissions, // IT
            6 => $this->juryPermissions, // Jury
        ];

        foreach ($permissionMapping as $roleId => $permissions) {
            $role = Role::find($roleId);

            if (empty($role)) {
                Log::warning("Role Id $roleId not found!");

                continue;
            }

            $permissions = array_map(fn (Permission $permission): array => ['permission' => $permission], $permissions);

            $role->permissions()->createMany($permissions);
        }

    }
}
