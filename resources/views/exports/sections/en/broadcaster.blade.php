@use(App\Enums\Broadcaster\BroadcasterConsent)
@use(App\Enums\Clips\ClipStatus)
@use(App\Enums\Broadcaster\BroadcasterPermission)
Your broadcaster settings.

| Field | Type | Description |
|---|---|---|
| `consent` | [BroadcasterConsent](#broadcasterconsent-enum)[] | Consent types you have granted |
| `default_clip_status` | [ClipStatus](#clipstatus-enum) | Status assigned to newly submitted clips |
| `twitch_mod_permissions` | [BroadcasterPermission](#broadcasterpermission-enum)[] | Twitch moderator permissions configured for your channel |
| `submission_rules` | `map<string, boolean>` | Who may submit clips to your channel |
| `onboarded_at` | datetime, nullable | When your manual onboarding was completed |
| `members[]` | object | see [Team Members](#team-members) |
| `filters[]` | object | see [Submission Filters](#submission-filters) |

#### Team Members

Users you have granted access to your Broadcaster Dashboard

| Field | Type | Description |
|---|---|---|
| `id` | integer | User ID |
| `permissions` | [BroadcasterPermission](#broadcasterpermission-enum)[] | The permissions you have granted |
| `created_at` | datetime | When you added the user |
| `updated_at` | datetime | When you last updated the user |

#### Submission Filters

Your configured submission filters.

| Field | Type | Description |
|---|---|---|
| `type` | string | What the filter applies to (e.g. a category) |
| `identifier` | integer | ID of the filtered entity |
| `state` | boolean | `true` when allowed, `false` when disallowed |
| `created_at` | datetime | When the filter was created |
| `updated_at` | datetime | When the filter was last changed |

@include('exports.partials.enum', ['enum' => BroadcasterConsent::class])
@include('exports.partials.enum', ['enum' => ClipStatus::class])
@include('exports.partials.enum', ['enum' => BroadcasterPermission::class])
