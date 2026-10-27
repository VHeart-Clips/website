@use(App\Enums\Broadcaster\BroadcasterPermission)
Broadcasters you have access to

| Field | Type | Description |
|---|---|---|
| `broadcaster_id` | integer | Broadcaster ID who you have access to |
| `permissions` | [BroadcasterPermission](#broadcasterpermission-enum)[] | Permissions granted to you by that broadcaster. |
| `created_at` | datetime | When you were added |

@include('exports.partials.enum', ['enum' => BroadcasterPermission::class])
