<a href="{{ route('marketplace.vendor.meta-ads.ad-sets.edit', $item->id) }}" class="btn btn-sm btn-outline-primary me-1">
    <x-core::icon name="ti ti-edit" />
</a>
<form action="{{ route('marketplace.vendor.meta-ads.ad-sets.destroy', $item->id) }}" method="POST" class="d-inline"
    onsubmit="return confirm('{{ __('Delete this ad set?') }}')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <x-core::icon name="ti ti-trash" />
    </button>
</form>
