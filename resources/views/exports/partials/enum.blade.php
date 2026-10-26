@use('Filament\Support\Contracts\HasDescription')
@use('Filament\Support\Contracts\HasLabel')
@props(['enum' => null, 'heading' => '####'])
@pushonce('enums', $enum)
@php
    $cell = static fn ($value) => str_replace(['|', "\n"], ['\|', ' '], (string) ($value ?? ''));

    $columns = [
        'Name' => static fn ($case) => "`$case->name`",
        'Value' => static fn ($case) => $case instanceof BackedEnum ? "`$case->value`" : '',
    ];

    if (is_a($enum, HasLabel::class, true)) {
        $columns['Label'] = static fn ($case) => $cell($case->getLabel());
    }

    if (is_a($enum, HasDescription::class, true)) {
        $columns['Description'] = static fn ($case) => $cell($case->getDescription());
    }
@endphp
{{ $heading }} {{ class_basename($enum) }} _Enum_

| {!! implode(' | ', array_keys($columns)) !!} |
|{!! str_repeat('---|', count($columns)) !!}
@foreach($enum::cases() as $case)
| {!! collect($columns)->map(fn ($column) => $column($case))->implode(' | ') !!} |
@endforeach
@endpushonce
