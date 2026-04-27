@php $related = $entry->getRelated(); @endphp

@if ($getChildComponentContainer()->getComponents())
    {{ $getChildComponentContainer() }}
@elseif ($related)
    <div class="fi-in-entry">
        <p class="fi-in-placeholder">{{ class_basename($related) }} #{{ $related->getKey() }}</p>
    </div>
@else
    <div class="fi-in-entry">
        <p class="fi-in-placeholder">{{ $entry->getPlaceholder() }}</p>
    </div>
@endif
