@props(['src' => null, 'alt' => null, 'viewBuffer' => 100, 'force' => false, 'fallback' => null, 'loading' => 'lazy', 'cookieName' => null])

@if($force)
    <div {{ $attributes }}>
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            @if($fallback) style="--fallback: url('{{ $fallback }}');" @endif
            class="h-full w-full object-cover text-transparent relative after:content-[''] after:absolute after:inset-0 after:bg-(image:--fallback) after:bg-cover after:bg-center"
            loading="{{ $loading }}"
            decoding="async"
        />
    </div>
@else
    @php
        $initialConsent = $cookieName
            ? \Whitecube\LaravelCookieConsent\Facades\Cookies::hasConsentFor($cookieName)
            : true;
    @endphp

    <div
        x-data="image(@js(['src' => $src, 'alt' => $alt, 'cookieName' => $cookieName, 'initialConsent' => $initialConsent], JSON_THROW_ON_ERROR))"
        x-intersect.margin.{{ $viewBuffer }}px.once="show()"
        {{ $attributes }}
    >
        @if(isset($placeholder))
            <div
                x-show="imageStatus === 'loading' && hasConsent()" {{ $placeholder->attributes->twMerge('absolute inset-0 flex items-center justify-center') }}>
                {{ $placeholder }}
            </div>
        @endif

        @if(isset($error))
            <div x-show="imageStatus === 'error'"
                 style="display: none;" {{ $error->attributes->twMerge('absolute inset-0 flex items-center justify-center') }}>
                {{ $error }}
            </div>
        @endif

            @if($cookieName)
                <div
                    x-show="!hasConsent()"
                    @if($initialConsent) style="display: none;" @endif
                    {{ $error->attributes->twMerge('absolute inset-0 flex flex-col gap-2 items-center justify-center') }}>
                    {{ $consent ?? '' }}
                    @empty($consent)
                        <x-lucide-cookie class="size-12 opacity-60" />
                        <p class="text-xs md:text-base text-center px-6 leading-relaxed md:opacity-0 group-hover:opacity-100 transition-all">
                            {{ __('Accept cookies to view this content.') }}
                        </p>
                    @endempty
                </div>
            @endif

        <template x-if="shown && hasConsent()">
            <img
                x-bind="imageBindings"
                x-init="checkCached($el)"
                class="h-full w-full object-cover opacity-0 data-[status=loaded]:opacity-100 transition-opacity data-[cached=false]:duration-300 data-[cached=true]:duration-150"
            />
        </template>

        @if($initialConsent)
        <noscript>
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                @if($fallback) style="--fallback: url('{{ $fallback }}');" @endif
                class="h-full w-full object-cover text-transparent relative after:content-[''] after:absolute after:inset-0 after:bg-(image:--fallback) after:bg-cover after:bg-center"
                loading="{{ $loading }}"
                decoding="async"
            />
        </noscript>
        @endif
    </div>
@endif
