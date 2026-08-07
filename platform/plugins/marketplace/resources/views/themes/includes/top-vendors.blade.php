@php
    $vendors = \Botble\Marketplace\Models\Store::getTopVendorsByMonthlySales(12);
    $vendors = $vendors->paginate(6);
@endphp

@if ($vendors->isNotEmpty())
    <section class="bb-related-store-section py-5">
        <div class="container">
            <div class="row mb-3">
                <div class="col-lg-12">
                    <h3 class="mb-0">{{ __('Top Vendors') }}</h3>
                    <p class="text-muted">{{ __('Discover our best performing vendors this month') }}</p>
                </div>
            </div>

            <div class="row g-4">
                @foreach ($vendors as $store)
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                        @include('plugins/marketplace::themes.includes.store-item', ['store' => $store])
                    </div>
                @endforeach
            </div>

            @if ($vendors->hasPages())
                <div class="row mt-5">
                    <div class="col-lg-12">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                @if ($vendors->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">{{ __('Previous') }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $vendors->previousPageUrl() }}">{{ __('Previous') }}</a>
                                    </li>
                                @endif

                                @foreach ($vendors->getUrlRange(1, $vendors->lastPage()) as $page => $url)
                                    @if ($page == $vendors->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                @if ($vendors->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $vendors->nextPageUrl() }}">{{ __('Next') }}</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">{{ __('Next') }}</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
