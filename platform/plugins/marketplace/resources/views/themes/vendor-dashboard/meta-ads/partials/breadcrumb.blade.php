<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('marketplace.vendor.meta-ads.dashboard') }}">Meta Ads</a></li>
        @foreach ($items as $item)
            @if ($loop->last)
                <li class="breadcrumb-item active">{{ $item['label'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
