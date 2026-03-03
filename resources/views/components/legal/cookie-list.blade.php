@use(Whitecube\LaravelCookieConsent\Facades\Cookies)
@props(['cookieLabel' => 'Cookie', 'descriptionLabel' => 'Description', 'durationLabel' => 'Duration'])

{{-- the markdown parser requires everything to be on the start of each line to work properly --}}
@foreach(Cookies::getCategories() as $category)
### {{ $category->title }}

{{ $category->description }}

| {{ $cookieLabel }} | {{ $descriptionLabel }} | {{ $durationLabel }} |
|---|---|---|
@foreach($category->getCookies() as $cookie)
| {{ $cookie->name }} | {{ $cookie->description }} | {{ \Carbon\CarbonInterval::minutes($cookie->duration)->cascade() }} |
@endforeach
@endforeach
