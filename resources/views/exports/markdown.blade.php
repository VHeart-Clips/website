@use(App\Enums\UserSettings\LanguagePreference)
# VHeart user data export

A copy of the personal data VHeart stores about you.

> **Do not share this file with anyone, including the VHeart Team.**

- **Exported at:** {{ $exportedAt }}
- **User ID:** {{ $user->id }}
- **Language:** {{ LanguagePreference::tryFrom(app()->currentLocale())?->getLabel() ?? app()->currentLocale() }}

Questions? Contact us via Tickets on our [Discord]({{ route('shorturl.redirect', ['slug' => 'discord']) }}) or see our [{{ __('footer.imprint.footer') }}]({{ route('imprint') }}) for other contact methods.

See [our privacy policy]({{ route('privacy') }}) for details on how we use and store your data.

## Table of contents

- [How to read this export](#how-to-read-this-export)
- [Sections](#sections)
@foreach ($sections as $section)
    - [{{ $section->title() }}](#{{ Str::slug($section->title(), language: app()->currentLocale()) }})
@endforeach
- [Enums](#enums)
@if(count($skippedSections) > 0)
- [Skipped Sections](#skipped-sections)
@endif

## How to read this export

- `data/` contains one UTF-8 JSON file per section below.
- `null` means not set, not applicable, or removed for privacy (e.g. other user data).
- Some enum values have labels. Labels are localized to your language at export time (shown above).
- Timestamps are ISO 8601 in UTC.

## Sections

@foreach ($sections as $section)
### {{ $section->title() }}

[`{{ $section->slug() }}.json`](data/{{ $section->slug() }}.json)

@includeFirst([
    'exports.sections.'.app()->currentLocale().'.'.$section->view(),
    'exports.sections.en.'.$section->view(),
    'exports.sections.'.$section->view(),
])

@endforeach

## Enums

@hasStack('enums')
@stack('enums')
@else
_No Enums in export._
@endif

@if(count($skippedSections) > 0)
## Skipped Sections

The following sections did not contain any data about you so we have skipped them in this export:

@foreach ($skippedSections as $section)
- {{ $section->title() }}
@endforeach
@endif
