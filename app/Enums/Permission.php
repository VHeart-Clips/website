<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\PermissionGroup as PermissionGroupEnum;
use App\Enums\Traits\HasHeadlineLabel;
use App\Support\Attributes\PermissionGroup as PermissionGroupAttribute;
use Filament\Support\Contracts\HasLabel;
use ReflectionClassConstant;

enum Permission: string implements HasLabel
{
    use HasHeadlineLabel;

    // User
    case ViewAnyUser = 'view_any_user';
    case UpdateAnyUser = 'update_any_user';
    case DeleteAnyUser = 'delete_any_user';
    case RestoreAnyUser = 'restore_any_user';
    case ForceDeleteAnyUser = 'force_delete_any_user';

    // Report
    case ViewAnyReport = 'view_any_report';
    case UpdateAnyReport = 'update_any_report';
    case DeleteAnyReport = 'delete_any_report';
    case RestoreAnyReport = 'restore_any_report';
    case ForceDeleteAnyReport = 'force_delete_any_report';

    // Compilation
    case ViewAnyCompilation = 'view_any_compilation';
    case CreateCompilation = 'create_compilation';
    case UpdateAnyCompilation = 'update_any_compilation';
    case DeleteAnyCompilation = 'delete_any_compilation';
    case RestoreAnyCompilation = 'restore_any_compilation';
    case ForceDeleteAnyCompilation = 'force_delete_any_compilation';

    // Category
    case ViewAnyCategory = 'view_any_category';
    case CreateCategory = 'create_category';
    case UpdateAnyCategory = 'update_any_category';

    // Faq Entry
    case ViewAnyFaqEntry = 'view_any_faq_entry';
    case CreateFaqEntry = 'create_faq_entry';
    case UpdateAnyFaqEntry = 'update_any_faq_entry';
    case DeleteAnyFaqEntry = 'delete_any_faq_entry';
    case RestoreAnyFaqEntry = 'restore_any_faq_entry';
    case ForceDeleteAnyFaqEntry = 'force_delete_any_faq_entry';

    // Comments
    case ViewAnyComment = 'view_any_comment';
    case CreateComment = 'create_comment';
    case DeleteAnyComment = 'delete_any_comment';

    // Tag
    case ViewAnyTag = 'view_any_tag';
    case CreateTag = 'create_tag';
    case UpdateAnyTag = 'update_any_tag';
    case DeleteAnyTag = 'delete_any_tag';

    // Roles
    case ViewAnyRole = 'view_any_role';
    case CreateRole = 'create_role';
    case UpdateAnyRole = 'update_any_role';
    case DeleteAnyRole = 'delete_any_role';

    // Clips
    case ViewAnyClip = 'view_any_clip';
    case CreateClip = 'create_clip';
    case UpdateAnyClip = 'update_any_clip';
    case DeleteAnyClip = 'delete_any_clip';
    case RestoreAnyClip = 'restore_any_clip';
    case ForceDeleteAnyClip = 'force_delete_any_clip';

    // Broadcaster
    case ViewAnyBroadcaster = 'view_any_broadcaster';
    case CreateBroadcaster = 'create_broadcaster';
    case UpdateAnyBroadcaster = 'update_any_broadcaster';
    case DeleteAnyBroadcaster = 'delete_any_broadcaster';
    case RestoreAnyBroadcaster = 'restore_any_broadcaster';
    case ForceDeleteAnyBroadcaster = 'force_delete_any_broadcaster';

    // BroadcasterConsentLog
    case ViewAnyBroadcasterConsentLog = 'view_any_broadcaster_consent_log';

    // BroadcasterSubmissionFilter
    case ViewAnyBroadcasterSubmissionFilter = 'view_any_broadcaster_submissinon_filter';
    case CreateAnyBroadcasterSubmissionFilter = 'create_any_broadcaster_submissinon_filter';
    case UpdateAnyBroadcasterSubmissionFilter = 'update_any_broadcaster_submissinon_filter';
    case DeleteAnyBroadcasterSubmissionFilter = 'delete_any_broadcaster_submissinon_filter';

    // BroadcasterTeamMember
    case ViewAnyBroadcasterTeamMember = 'view_any_broadcaster_team_member';
    case CreateAnyBroadcasterTeamMember = 'create_any_broadcaster_team_member';
    case UpdateAnyBroadcasterTeamMember = 'update_any_broadcaster_team_member';
    case DeleteAnyBroadcasterTeamMember = 'delete_any_broadcaster_team_member';

    // RemovalRequest
    case ViewAnyRemovalRequest = 'view_any_broadcaster_removal_request';
    case UpdateAnyRemovalRequest = 'update_any_broadcaster_removal_request';
    case DeleteAnyRemovalRequest = 'delete_any_broadcaster_removal_request';

    // Audit
    case ViewAnyAudit = 'view_any_audit';

    // Non-Model stuff
    case JuryVote = 'jury_vote';

    #[PermissionGroupAttribute(PermissionGroupEnum::AdminSubmission)]
    case BypassConsentCheck = 'as_bypass_consent';
    #[PermissionGroupAttribute(PermissionGroupEnum::AdminSubmission)]
    case BypassMaximumAgeLimitCheck = 'as_bypass_max_age';

    #[PermissionGroupAttribute(PermissionGroupEnum::AdminSubmission)]
    case BypassMinimumLengthRequirementCheck = 'as_bypass_min_length';

    #[PermissionGroupAttribute(PermissionGroupEnum::AdminSubmission)]
    case BypassBannedCategoryCheck = 'as_bypass_banned_category';

    #[PermissionGroupAttribute(PermissionGroupEnum::ClipManagement)]
    case CanSubmitClipFeedback = 'can_submit_clip_feedback';

    #[PermissionGroupAttribute(PermissionGroupEnum::Moderation)]
    case CanFlagClips = 'can_flag_clips';

    #[PermissionGroupAttribute(PermissionGroupEnum::Moderation)]
    case CanUnflagClips = 'can_unflag_clips';

    case CanImportUsers = 'can_import_user';

    public function getPermissionGroup(): string
    {
        static $cache = [];

        if (array_key_exists($this->name, $cache)) {
            return $cache[$this->name];
        }

        if (($attribute = $this->getPermissionAttribute()) instanceof PermissionGroupEnum) {
            return $cache[$this->name] = $attribute->getLabel();
        }

        static $prefixes = [
            'force_delete_any_', 'force_delete_',
            'restore_any_', 'restore_',
            'delete_any_', 'delete_',
            'update_any_', 'update_',
            'view_any_', 'view_',
            'create_any_', 'create_',
        ];

        $groupName = str_replace($prefixes, '', $this->value, $count);

        if ($count === 0) {
            return $cache[$this->name] = PermissionGroupEnum::Other->getLabel();
        }

        return $cache[$this->name] = ucwords(str_replace('_', ' ', $groupName));
    }

    private function getPermissionAttribute(): ?PermissionGroupEnum
    {
        $reflection = new ReflectionClassConstant(self::class, $this->name);
        $attributes = $reflection->getAttributes(PermissionGroupAttribute::class);

        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance()->name;
    }
}
