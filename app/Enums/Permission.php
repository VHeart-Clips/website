<?php

namespace App\Enums;

// could be worth it using integer instead to optimize storage usage lol
enum Permission: string
{
    case Edit_Users = 'edit_users';
    case Edit_Clips = 'edit_clips';
}
