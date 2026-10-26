Your account details.

| Field | Type | Description |
|---|---|---|
| `id` | integer | Your Twitch user ID |
| `name` | string | Twitch Display name |
| `avatar_url` | string, nullable | URL of your avatar |
| `email` | string, nullable | Email address |
| `email_verified_at` | datetime, nullable | When the email was verified |
| `roles` | string[] | Roles assigned to your account |
| `language` | [LanguagePreference](#languagepreference-enum), nullable | The language you have currently selected |
| `two_factor.has_email_authentication` | boolean | Whether email authentication is enabled |
| `two_factor.has_app_authentication` | boolean | Whether authenticator app authentication is enabled |
| `created_at` | datetime | When your account was created |
| `updated_at` | datetime | When your account was updated |
| `deleted_at` | datetime, nullable | When the user was removed |

@include('exports.partials.enum', ['enum' => \App\Enums\UserSettings\LanguagePreference::class])
