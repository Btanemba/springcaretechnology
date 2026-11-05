@php($url = $entry->{$column['name']})

@if ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $url }}</a>
@else
    <span>-</span>
@endif