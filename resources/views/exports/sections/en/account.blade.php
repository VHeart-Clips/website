@use(App\Enums\UserSettings\LanguagePreference)
@use(App\Enums\UserSettings\NewGameAvoidancePreference)
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
| `preferences[]` | `map<string, mixed>`, nullable | see [User Preferences](#user-preferences) |
| `two_factor.has_email_authentication` | boolean | Whether email authentication is enabled |
| `two_factor.has_app_authentication` | boolean | Whether authenticator app authentication is enabled |
| `created_at` | datetime | When your account was created |
| `updated_at` | datetime | When your account was updated |
| `deleted_at` | datetime, nullable | When the user was removed |

#### User Preferences

The preferences you have set, if you never have set any preferences this object will be `null` and we use default values.

| Field | Type | Description |
|---|---|---|
| `user_id` | integer | Your Twitch user ID |
| `show_on_leaderboard` | boolean, nullable | whether you want to be visible on the leaderboard or not, `null` if default |
| `avoid_spoilers` | boolean, nullable | whether you want to avoid spoilers while voting, `null` if default |
| `avoid_new_games` | [NewGameAvoidancePreference](#newgameavoidancepreference-enum)[], nullable | whether you want to avoid recently released games, `null` if disabled |
| `created_at` | datetime | When first changed your preferences |
| `updated_at` | datetime | When you last updated your preferences |

@include('exports.partials.enum', ['enum' => LanguagePreference::class])
@include('exports.partials.enum', ['enum' => NewGameAvoidancePreference::class])
