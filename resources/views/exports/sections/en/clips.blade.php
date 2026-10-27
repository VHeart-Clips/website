@use(App\Enums\Clips\ClipStatus)
Clips you submitted, broadcast or created.

Clip data is provided by Twitch: [Twitch/Get Clips](https://dev.twitch.tv/docs/api/reference/#get-clips).

some values may be corrected by us later (e.g. wrong category).

| Field | Type | Description |
|---|---|---|
| `id` | string | Unique clip ID. |
| `title` | string | Clip title. |
| `thumbnail_url` | string | URL of the clip thumbnail. |
| `status` | [ClipStatus](#clipstatus-enum) | Moderation status. |
| `duration` | float | Length in seconds, precision 0.1. |
| `language` | string | Language code of the broadcaster's stream. |
| `vod_id` | integer, nullable | Source video ID (VOD). `null` if twitch did not provide any. |
| `vod_offset` | integer, nullable | Zero-based start offset in the VOD, in seconds. |
| `tags` | string[] | Clip tags. |
| `category_id` | integer | Game/Category ID |
| `broadcaster_id` | integer | User ID of the broadcaster. |
| `creator_id` | integer | User ID of the clip creator. |
| `submitter_id` | integer | User ID of the submitter. |
| `clipped_at` | datetime | When the clip was created on Twitch. |
| `created_at` | datetime | When the clip was submitted. |
| `updated_at` | datetime | When the record was last changed |
| `deleted_at` | datetime, nullable | When the clip was removed |
| `next_refresh_after` | datetime, nullable | Earliest next sync from Twitch. |

@include('exports.partials.enum', ['enum' => ClipStatus::class])
