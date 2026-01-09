<?php

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

    // Compilation
    case ViewAnyCompilation = 'view_any_compilation';
    case ViewCompilation = 'view_compilation';
    case CreateCompilation = 'create_compilation';
    case UpdateAnyCompilation = 'update_any_compilation';
    case DeleteAnyCompilation = 'delete_any_compilation';
    case RestoreAnyCompilation = 'restore_any_compilation';
    case ForceDeleteAnyCompilation = 'force_delete_any_compilation';

    // Non-Model stuff
    // empty for now
}
