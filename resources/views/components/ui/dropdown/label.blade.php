@props(['inset' => null])
<div @if($inset) data-inset="true" @endif {{ $attributes->twMerge('px-2 py-1.5 text-sm font-medium data-[inset]:pl-8') }}
>{{ $slot }}</div>
