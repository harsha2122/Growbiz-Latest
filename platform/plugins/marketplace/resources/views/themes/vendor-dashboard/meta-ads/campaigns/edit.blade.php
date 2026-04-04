@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Campaign</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.campaigns.update', $campaign->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $campaign->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Objective <span class="text-danger">*</span></label>
                    <select name="objective" class="form-select" required>
                        @foreach(['OUTCOME_TRAFFIC' => 'Traffic', 'OUTCOME_AWARENESS' => 'Awareness', 'OUTCOME_ENGAGEMENT' => 'Engagement', 'OUTCOME_SALES' => 'Sales'] as $val => $label)
                            <option value="{{ $val }}" {{ old('objective', $campaign->objective) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Daily Budget ($)</label>
                        <input type="number" name="daily_budget" class="form-control" min="1" step="0.01"
                               value="{{ old('daily_budget', $campaign->daily_budget) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lifetime Budget ($)</label>
                        <input type="number" name="lifetime_budget" class="form-control" min="1" step="0.01"
                               value="{{ old('lifetime_budget', $campaign->lifetime_budget) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control"
                               value="{{ old('start_date', $campaign->start_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control"
                               value="{{ old('end_date', $campaign->end_date?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
