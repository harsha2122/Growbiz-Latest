<a href="{{ route('marketplace.vendor.meta-ads.campaigns.edit', $item->id) }}" class="btn btn-sm btn-outline-primary me-1">
    <x-core::icon name="ti ti-edit" />
</a>

@if ($item->status === 'ACTIVE')
    <form action="{{ route('marketplace.vendor.meta-ads.campaigns.pause', $item->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-warning me-1" title="{{ __('Pause') }}">
            <x-core::icon name="ti ti-player-pause" />
        </button>
    </form>
@else
    <form action="{{ route('marketplace.vendor.meta-ads.campaigns.resume', $item->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-success me-1" title="{{ __('Resume') }}">
            <x-core::icon name="ti ti-player-play" />
        </button>
    </form>
@endif

<form action="{{ route('marketplace.vendor.meta-ads.campaigns.destroy', $item->id) }}" method="POST" class="d-inline"
    onsubmit="return confirm('{{ __('Delete this campaign?') }}')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <x-core::icon name="ti ti-trash" />
    </button>
</form>
