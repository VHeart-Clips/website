<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\HasHeadlineLabel;
use Filament\Support\Contracts\HasLabel;

enum Permission: string implements HasLabel
{
    use HasHeadlineLabel;

    // User
    case ViewAnyUser = 'view_any_user';
    case ViewUser = 'view_user';
    case CreateUser = 'create_user';
    case UpdateAnyUser = 'update_any_user';
    case DeleteAnyUser = 'delete_any_user';
    case RestoreAnyUser = 'restore_any_user';
    case ForceDeleteAnyUser = 'force_delete_any_user';

    // Report
    case ViewAnyReport = 'view_any_report';
    case ViewReport = 'view_report';
    case CreateReport = 'create_report';
    case UpdateAnyReport = 'update_any_report';
    case DeleteAnyReport = 'delete_any_report';
    case RestoreAnyReport = 'restore_any_report';
    case ForceDeleteAnyReport = 'force_delete_any_report';
    // Compilation
    case ViewAnyCompilation = 'view_any_compilation';
    case ViewCompilation = 'view_compilation';
    case CreateCompilation = 'create_compilation';
    case UpdateAnyCompilation = 'update_any_compilation';
    case DeleteAnyCompilation = 'delete_any_compilation';
    case RestoreAnyCompilation = 'restore_any_compilation';
    case ForceDeleteAnyCompilation = 'force_delete_any_compilation';

    // Non-Model stuff

    case JuryVote = 'jury_vote';
    // empty for now
}
