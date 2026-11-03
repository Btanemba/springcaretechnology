@php($photoUrl = $unit?->photoUrls()[0] ?? null)
<div class="device-photo {{ $photoClass ?? '' }}">
    @if($photoUrl)
        <img src="{{ $photoUrl }}" alt="{{ $alt }}" loading="lazy" width="640" height="640" data-device-photo>
    @endif
    <div class="photo-placeholder" @if($photoUrl) hidden @endif><i data-lucide="image"></i><span>Photos coming soon</span></div>
</div>