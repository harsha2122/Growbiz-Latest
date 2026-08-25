@if ($product->views > 0)
    @once
        <style>
            .bb-product-views-count {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: .75rem;
                color: #6c757d;
                margin-top: 4px;
            }
            .bb-product-views-count svg {
                flex-shrink: 0;
            }
            .bb-product-views-count .bb-live-dot {
                position: relative;
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: #22c55e;
                flex-shrink: 0;
            }
            .bb-product-views-count .bb-live-dot::before {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: 50%;
                background-color: #22c55e;
                animation: bb-live-dot-ping 1.6s cubic-bezier(0, 0, 0.2, 1) infinite;
            }
            @keyframes bb-live-dot-ping {
                0% {
                    transform: scale(1);
                    opacity: .7;
                }
                75%, 100% {
                    transform: scale(3);
                    opacity: 0;
                }
            }
            @media (prefers-reduced-motion: reduce) {
                .bb-product-views-count .bb-live-dot::before {
                    animation: none;
                }
            }
        </style>
    @endonce

    <div class="bb-product-views-count">
        <span class="bb-live-dot"></span>
        {{ __(':count viewed', ['count' => number_format($product->views)]) }}
    </div>
@endif
