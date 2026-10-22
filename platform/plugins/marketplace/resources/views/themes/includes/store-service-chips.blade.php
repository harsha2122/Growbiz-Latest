@php
    $storeServiceTags = $store->serviceTagsList();
@endphp

@once
    <style>
        .bb-store-chips-wrap {
            padding: 20px 0 10px;
        }

        .bb-store-chips-title {
            font-size: 16px;
            font-weight: 700;
            color: #050505;
            margin-bottom: 12px;
        }

        .bb-store-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .bb-store-chip {
            display: inline-flex;
            align-items: center;
            padding: 8px 18px;
            border-radius: 999px;
            background-color: #f0f2f5;
            color: #050505;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
@endonce

<div class="bb-store-chips-wrap">
    <div class="bb-store-chips-title">{{ __('Services') }}</div>

    @if (! empty($storeServiceTags))
        <div class="bb-store-chips">
            @foreach ($storeServiceTags as $tag)
                <span class="bb-store-chip">{{ $tag }}</span>
            @endforeach
        </div>
    @else
        <p class="text-muted mb-0">{{ __('This seller has not listed any services yet.') }}</p>
    @endif
</div>
