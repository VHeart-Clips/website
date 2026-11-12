<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Broadcaster\BroadcasterConsentLog;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Kirschbaum\Commentions\CommentReaction;
use Kirschbaum\Commentions\CommentSubscription;

#[Tries(3)]
#[Backoff(60)]
class RemoverUserDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $userId,
    ) {}

    public function handle(): void
    {
        $user = User::onlyTrashed()
            ->with([
                'broadcaster',
            ])
            ->find($this->userId);

        if (! $user) {
            return;
        }

        if ($broadcaster = $user->broadcaster) {
            DB::transaction(static function () use ($broadcaster): void {
                $broadcaster->members()->delete();
                $broadcaster->filters()->delete();

                if ($broadcaster->consent?->isNotEmpty()) {
                    BroadcasterConsentLog::create([
                        'broadcaster_id' => $broadcaster->id,
                        'state' => [],
                        'change_reason' => 'User Deleted',
                        'changed_by' => 0,
                        'changed_at' => now(),
                    ]);
                }

                $broadcaster->forceDeleteQuietly();
            });
        }

        $user->broadcasterTeamMembers()->delete();
        $user->notifications()->delete();
        $user->preferences()->delete();
        $user->ignoredClips()->delete();

        CommentSubscription::query()
            ->where('subscriber_id', $this->userId)
            ->where('subscribable_type', (new User)->getMorphClass())
            ->delete();

        $user->updateQuietly([
            'email' => null,
            'email_verified_at' => null,
            'avatar_url' => null,
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
            'has_email_authentication' => false,
            'twitch_refresh_token' => null,
        ]);

        $hasReferences =
            $user->votes()->exists()
            || $user->comments()->exists()
            || Compilation::query()
                ->where('user_id', $user->id)
                ->exists()
            || CompilationClip::query()
                ->where('added_by', $user->id)
                ->orWhere('claimed_by', $user->id)
                ->exists()
            || BroadcasterSubmissionFilter::query()
                ->where('filterable_id', $user->id)
                ->where('filterable_type', (new User)->getMorphClass())
                ->exists()
            || Report::query()
                ->where(fn (Builder $builder) => $builder->where('reportable_id', $user->id)->where('reportable_type', (new User)->getMorphClass()))
                ->orWhere('user_id', $user->id)
                ->orWhere('claimed_by', $user->id)
                ->exists();

        if (! $hasReferences) {
            CommentReaction::query()
                ->where('reactor_id', $this->userId)
                ->where('reactor_type', (new User)->getMorphClass())
                ->delete();

            // If there are no foreign key references we can safely force delete the user
            $user->forceDelete();
        }
    }
}
