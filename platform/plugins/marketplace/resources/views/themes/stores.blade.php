<div class="container">
    <h3>{{ __('Our Stores') }}</h3>

    <div class="row">
        @foreach ($stores as $store)
            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
                @include('plugins/marketplace::themes.includes.store-item')
            </div>
        @endforeach
    </div>

    {!! $stores->withQueryString()->links() !!}
</div>
