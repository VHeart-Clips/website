<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;

class CommentPolicy extends \Kirschbaum\Commentions\Policies\CommentPolicy
{
    public function create(Commenter $user): bool
    {
        if ($user->getKey() === 0) {
            return true;
        }

        return $user->can(Permission::CreateComment);
    }

    /**
     * @param  Commenter  $user
     */
    public function update($user, Comment $comment): bool
    {
        // Lock Comments that are over 1 day ago, enough time to fix typos or whatever
        if ($comment->getCreatedAt() < now()->subDay()) {
            return false;
        }

        return $comment->isAuthor($user);
    }

    /**
     * @param  Commenter  $user
     */
    public function delete($user, Comment $comment): bool
    {
        if ($user->can(Permission::DeleteAnyComment)) {
            return true;
        }

        return $comment->isAuthor($user);
    }
}
