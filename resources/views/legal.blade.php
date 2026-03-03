{{-- this should kept simple overall, no fancy colors or anything that makes it harder to read in any way, plain text on plain background --}}
{{-- with plain typography rules --}}
<x-layout :title="__('legal.title.' . $type)" :background="false">
    <x-markdown blade="true" class="max-w-7xl m-auto prose dark:prose-invert text-balance my-6 [&_table]:block lg:[&_table]:table [&_table]:overflow-x-auto [&_table]:whitespace-nowrap">
        @include('legal.' . $locale . '.' . $type)
    </x-markdown>
</x-layout>
