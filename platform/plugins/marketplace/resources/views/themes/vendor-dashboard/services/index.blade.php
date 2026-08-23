@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Services') }}</h4>
        <a href="{{ route('marketplace.vendor.services.create') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" /> {{ __('Add New') }}
        </a>
    </div>

    @if ($services->isEmpty())
        <div class="alert alert-info">{{ __('No services found.') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Price') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Book Now Clicks (Today)') }}</th>
                        <th>{{ __('Book Now Clicks (30d)') }}</th>
                        <th>{{ __('Book Now Clicks (Total)') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->price ? format_price($service->price) : __('Contact for price') }}</td>
                            <td>{!! $service->status->toHtml() !!}</td>
                            <td>{{ number_format($service->clicksCount('today')) }}</td>
                            <td>{{ number_format($service->clicksCount('30days')) }}</td>
                            <td>{{ number_format($service->clicksCount()) }}</td>
                            <td>
                                <a href="{{ route('marketplace.vendor.services.edit', $service->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <x-core::icon name="ti ti-edit" />
                                </a>
                                <form action="{{ route('marketplace.vendor.services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
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

        {{ $services->links() }}
    @endif
@endsection
