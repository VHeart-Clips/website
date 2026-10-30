Actions you performed and actions performed on your account.

For logins, we store your full IP address and browser information.

For all other actions, we store only a shortened IP address (the last half is removed) and a hashed version of your browser information. We keep this only to detect abuse.

For clips, the ID shown is the Twitch clip ID instead of our internal ID.

IP address and browser information are deleted 30 days after the action.

| Field | Type | Description |
|---|---|---|
| `caused_by_you` | boolean | Whether you performed the action |
| `auditable_type` | string | What kind of entity the action was performed on |
| `auditable_id` | string, integer, nullable, omittable | The ID of the entity the action was performed on. For clips this is the Twitch clip ID. |
| `event` | string | What happened (e.g. created, updated, deleted) |
| `changed` | string[], nullable, omittable | Which fields were changed. |
| `tags` | string[], nullable, omittable | Tags attached to the action. |
| `request_id` | string, nullable, omittable | Internal ID of the request. |
| `ip_address` | string, nullable, omittable | The IP address the action came from. Shortened for non-login actions. `null` 30 days after the action was performed. |
| `user_agent` | string, nullable, omittable | Your browser and device information. Hashed for non-login actions. `null` 30 days after the action was performed |
| `created_at` | datetime | When the action was performed |
| `updated_at` | datetime | When the record was last changed |
