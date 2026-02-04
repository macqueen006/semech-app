<div class="space-y-4 w-full">
    @foreach($advertisements as $ad)
        <div class="mb-4 last:mb-0 @if($ad->size === 'small') max-h-32 @elseif($ad->size === 'medium') max-h-48 @elseif($ad->size === 'large') max-h-64 @elseif($ad->size === 'banner') max-h-40 @endif">
            @if($ad->link_url)
                <a
                    href="{{ $ad->link_url }}"
                    @if($ad->open_new_tab) target="_blank" rel="noopener noreferrer" @endif
                    onclick="trackAdClick({{ $ad->id }})"
                    class="block group"
                >
                    @if($ad->image_path)
                        <img
                            src="{{ $ad->image_path }}"
                            alt="{{ $ad->title }}"
                            class="w-full h-full rounded-sm transition-transform duration-300 group-hover:scale-105 @if($ad->size === 'small') max-h-32 @elseif($ad->size === 'medium') max-h-48 @elseif($ad->size === 'large') max-h-64 @elseif($ad->size === 'banner') max-h-40 @endif object-cover"
                        >
                    @endif
                </a>
            @else
                @if($ad->image_path)
                    <img
                        src="{{ $ad->image_path }}"
                        alt="{{ $ad->title }}"
                        class="w-full h-full rounded-sm @if($ad->size === 'small') max-h-32 @elseif($ad->size === 'medium') max-h-48 @elseif($ad->size === 'large') max-h-64 @elseif($ad->size === 'banner') max-h-40 @endif object-cover"
                    >
                @endif
            @endif
        </div>
    @endforeach
</div>

@once
    @push('scripts')
        <script>
            function trackAdClick(adId) {
                // Track click asynchronously using Beacon API (doesn't block navigation)
                if (navigator.sendBeacon) {
                    const data = new FormData();
                    data.append('advertisement_id', adId);
                    navigator.sendBeacon('{{ route("track-ad-click") }}', data);
                } else {
                    // Fallback for older browsers
                    fetch('{{ route("track-ad-click") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ advertisement_id: adId }),
                        keepalive: true
                    }).catch(err => console.error('Ad tracking failed:', err));
                }
            }
        </script>
    @endpush
@endonce
