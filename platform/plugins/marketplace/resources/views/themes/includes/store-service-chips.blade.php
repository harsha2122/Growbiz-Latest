@php
    $storeServiceTags = $store->serviceTagsList();
@endphp

@if ($store->is_minimal_profile && ! empty($storeServiceTags))
@once
    <style>
        .bb-store-chips-wrap {
            padding: 24px 0 14px;
        }

        .bb-store-chips-title {
            font-size: 18px;
            font-weight: 700;
            color: #050505;
            margin-bottom: 14px;
        }

        .bb-store-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .bb-store-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            max-width: 100%;
            overflow-wrap: anywhere;
            padding: 10px 20px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .bb-store-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
        }

        .bb-store-chip svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* Cycle through a small accent palette so the row doesn't read as one flat grey block. */
        .bb-store-chip:nth-child(6n+1) { background-color: #eef4ff; color: #2b6ff2; border-color: #d9e6ff; }
        .bb-store-chip:nth-child(6n+2) { background-color: #fff1ee; color: #ef5a3c; border-color: #ffdfd7; }
        .bb-store-chip:nth-child(6n+3) { background-color: #ecfbf3; color: #17a865; border-color: #d4f3e2; }
        .bb-store-chip:nth-child(6n+4) { background-color: #fdf3ea; color: #cc7a1a; border-color: #fbe3c8; }
        .bb-store-chip:nth-child(6n+5) { background-color: #f5edff; color: #7c3aed; border-color: #e7d7ff; }
        .bb-store-chip:nth-child(6n+6) { background-color: #fdedf4; color: #d23a79; border-color: #fad2e3; }
    </style>
@endonce

<div class="bb-store-chips-wrap">
    <div class="bb-store-chips-title">{{ __('Services') }}</div>

        <div class="bb-store-chips">
            @foreach ($storeServiceTags as $tag)
                <span class="bb-store-chip">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.59 13.41L13.42 20.58C13.2343 20.766 13.0137 20.9135 12.7709 21.0141C12.5281 21.1148 12.2678 21.1666 12.005 21.1666C11.7422 21.1666 11.4819 21.1148 11.2391 21.0141C10.9963 20.9135 10.7757 20.766 10.59 20.58L2 12V2H12L20.59 10.59C20.9625 10.9647 21.1716 11.4716 21.1716 12C21.1716 12.5284 20.9625 13.0353 20.59 13.41Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 7H7.01" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $tag }}
                </span>
            @endforeach
        </div>
</div>

@endif
