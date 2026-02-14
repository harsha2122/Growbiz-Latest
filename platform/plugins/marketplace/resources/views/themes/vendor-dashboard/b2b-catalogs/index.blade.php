@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('B2B Catalogs') }}</h4>
        <a href="{{ route('marketplace.vendor.b2b-catalogs.create') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" /> {{ __('Add New') }}
        </a>
    </div>

    @if ($catalogs->isEmpty())
        <div class="alert alert-info">{{ __('No B2B catalogs found.') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Discount') }}</th>
                        <th>{{ __('PDF') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($catalogs as $catalog)
                        <tr>
                            <td>{{ $catalog->id }}</td>
                            <td>{{ $catalog->title }}</td>
                            <td>{{ Str::limit($catalog->description, 80) }}</td>
                            <td>
                                @if ($catalog->discount_percentage > 0)
                                    <span class="badge bg-danger">{{ rtrim(rtrim(number_format($catalog->discount_percentage, 2), '0'), '.') }}% OFF</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('marketplace.vendor.b2b-catalogs.view-pdf', $catalog->id) }}" class="btn btn-sm btn-outline-primary">
                                    <x-core::icon name="ti ti-eye" /> {{ __('View PDF') }}
                                </a>
                            </td>
                            <td>{{ $catalog->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('marketplace.vendor.b2b-catalogs.edit', $catalog->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <x-core::icon name="ti ti-edit" />
                                </a>
                                <form action="{{ route('marketplace.vendor.b2b-catalogs.destroy', $catalog->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <x-core::icon name="ti ti-trash" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $catalogs->links() }}
    @endif
@endsection
