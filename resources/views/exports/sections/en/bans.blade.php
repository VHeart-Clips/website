Bans issued against your account.

User IDs (`admin_id` and `unbanned_by`) have been omitted for privacy.

| Field | Type | Description |
|---|---|---|
| `id` | integer | Ban ID |
| `type` | string | Banned Entity (e.g. `user` or `broadcaster`) |
| `reason` | string | Reason for your Ban |
| `banned_until` | datetime, nullable | When the ban expires, `null` if permanent |
| `unbanned_at` | datetime, nullable | When the ban was manually lifted |
| `created_at` | datetime | When the ban was created |
| `updated_at` | datetime | When the ban was last changed |
