@if ($product->views > 0)
    @once
        <style>
            .bb-product-views-count {
                display: flex;
                align-items: center;
                gap: 4px;
                font-size: .75rem;
                color: #6c757d;
                margin-top: 4px;
            }
            .bb-product-views-count svg {
                flex-shrink: 0;
            }
        </style>
    @endonce

    <div class="bb-product-views-count">
        <x-core::icon name="ti ti-eye" />
        {{ __(':count viewed', ['count' => number_format($product->views)]) }}
    </div>
@endif
