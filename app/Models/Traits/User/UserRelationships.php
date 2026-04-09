<?php

declare(strict_types=1);

namespace App\Models\Traits\User;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterTeamMember;
use App\Models\Clip;
use App\Models\Role;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin User
 */
trait UserRelationships
{
    /**
     * @return BelongsToMany<Role, $this, Pivot>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function broadcastedClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'broadcaster_id');
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function createdClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'creator_id');
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function submittedClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'submitter_id');
    }

    /**
     * @return HasOne<Broadcaster, $this>
     */
    public function broadcaster(): HasOne
    {
        return $this->hasOne(Broadcaster::class, 'id');
    }

    /**
     * @return HasMany<BroadcasterTeamMember, $this>
     */
    public function broadcasterTeamMembers(): HasMany
    {
        return $this->hasMany(BroadcasterTeamMember::class);
    }

    /*
     * Hook into some relationship logic to clear our cache
     */
    public function setRelation($relation, $value): self
    {
        if ($relation === 'roles') {
            $this->permissionCache = null;
            $this->importantRoleCache = null;
        }

        return parent::setRelation($relation, $value);
    }
}
