<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
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

    // Non-Model stuff
    // empty for now
}
