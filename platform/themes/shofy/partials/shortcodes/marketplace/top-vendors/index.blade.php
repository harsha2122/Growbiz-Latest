<section class="tp-stores-area">
    <div class="container">
        @if($shortcode->title || $shortcode->subtitle)
            <div class="mb-40">
                {!! Theme::partial('section-title', compact('shortcode')) !!}
            </div>
        @endif

        <div class="row g-4 mb-40">
            @foreach ($stores as $store)
                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
                    @include('plugins/marketplace::themes.includes.store-item')
                </div>
            @endforeach
        </div>
    </div>
</section>
