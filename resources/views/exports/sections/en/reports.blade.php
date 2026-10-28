@use(App\Enums\Reports\ReportStatus)
@use(App\Enums\Reports\ResolveAction)
Reports you created and reports created by others about you or your content.

> Only reports you created include all fields, reports created by others about you or your content are limited for privacy and moderation reasons.

| Field | Type | Description |
|---|---|---|
| `id` | integer | Report ID, provide this in a Discord Ticket for questions about this report. |
| `created_by_you` | boolean | Whether you created the report |
| `category_id` | integer, nullable | The report category ID |
| `category` | string, nullable | The report category name (translated in your export language) |
| `reportable_type` | string | The kind of entity the report is about |
| `reportable_id` | integer\|string, nullable | The ID of the entity, `null` if the entity no longer exists. |
| `description` | string, nullable, omittable | Your report description |
| `status` | [ReportStatus](#reportstatus-enum) | Current processing status |
| `resolve_action` | [ResolveAction](#resolveaction-enum), nullable | Action taken, if resolved |
| `reported_at` | datetime, nullable, omittable | When you filed the report |
| `resolved_at` | datetime, nullable | When the report was resolved |
| `deleted_at` | datetime, nullable, omittable | When the report got deleted |

@include('exports.partials.enum', ['enum' => ReportStatus::class])
@include('exports.partials.enum', ['enum' => ResolveAction::class])
