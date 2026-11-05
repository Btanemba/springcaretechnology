@php($url = $entry->{$column['name']})

@if ($url)
    <a href="{{ $url }}" title="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ parse_url($url, PHP_URL_HOST) ?: $url }}</a>
@else
    <span>-</span>
@endif