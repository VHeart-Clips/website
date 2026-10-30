@use(App\Enums\ClipVoteType)
Your Votes.

You can view and change your last vote on your [Vote Statistic]({{ route('user.statistics.votes') }}) page.

| Field | Type | Description |
|---|---|---|
| `clip_id` | string | Clip you voted on |
| `type` | [ClipVoteType](#clipvotetype-enum) | Vote type |
| `state` | boolean | `true` if voted for it, `false` otherwise |
| `created_at` | datetime | When the vote was created |
| `updated_at` | datetime | When the vote was updated |

@include('exports.partials.enum', ['enum' => ClipVoteType::class])
