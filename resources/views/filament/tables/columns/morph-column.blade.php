@php
    $related = $column->getRelated();
    $resolved = $column->getResolvedColumns();
    $table = $column->getTable();
    $record = $getRecord();
    $recordKey = $column->getRecordKey();
    $rowLoop = $column->getRowLoop();
    $placeholder = $column->getPlaceholder();
@endphp

@if ($resolved)
    @foreach ($resolved as $child)
        {!! $child->table($table)->record($record)->recordKey($recordKey)->rowLoop($rowLoop)->renderInLayout() !!}
    @endforeach
@elseif ($related)
    {{ class_basename($related) }} #{{ $related->getKey() }}
@elseif(filled($placeholder))
    <p class="fi-ta-placeholder">
        {{ $placeholder }}
    </p>
@endif
