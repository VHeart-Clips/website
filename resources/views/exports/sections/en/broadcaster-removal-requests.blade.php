@use(App\Enums\Broadcaster\RemovalRequestStatus)
Clip removal requests you made.

| Field | Type | Description |
|---|---|---|
| `clip_id` | string, nullable | Clip the request is about, `null` if the clip has been removed from our platform |
| `status` | [RemovalRequestStatus](#removalrequeststatus-enum) | Current processing status |
| `details` | string | Your details for this removal request |
| `requested_at` | datetime | When you made the request |
| `resolved_at` | datetime, nullable | When the request was resolved |

@include('exports.partials.enum', ['enum' => RemovalRequestStatus::class])
