Your Broadcaster consent history.

| Field | Type | Description |
|---|---|---|
| `state` | [BroadcasterConsent](#broadcasterconsent-enum)[] | Your Consent |
| `changed_by` | string | `you`, `system` (e.g. on account deletion) or `other` (another user, e.g. a team member or administrator) |
| `reason` | string | Reason for the Consent change |
| `changed_at` | datetime | When the change happened |

@include('exports.partials.enum', ['enum' => \App\Enums\Broadcaster\BroadcasterConsent::class])
