@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @if ($totalProducts)
        <div class="text-start text-sm-end mb-5">
            <x-core::button
                type="button"
                color="primary"
                :outlined="true"
                class="date-range-picker"
                :data-format-value="trans('plugins/ecommerce::reports.date_range_format_value', ['from' => '__from__', 'to' => '__to__'])"
                :data-format="Str::upper(config('core.base.general.date_format.js.date'))"
                :data-href="route('marketplace.vendor.dashboard')"
                :data-start-date="$data['startDate']"
                :data-end-date="$data['endDate']"
                icon="ti ti-calendar"
            >
                {{ trans('plugins/ecommerce::reports.date_range_format_value', [
                    'from' => BaseHelper::formatDate($data['startDate']),
                    'to' => BaseHelper::formatDate($data['endDate']),
                ]) }}
            </x-core::button>
        </div>
    @endif

    <section
        class="ps-dashboard report-chart-content"
        id="report-chart"
    >
        @include(MarketplaceHelper::viewPath('vendor-dashboard.partials.dashboard-content'))
    </section>
@stop

@push('footer')
    <script>
        'use strict';

        var BotbleVariables = BotbleVariables || {};
        BotbleVariables.languages = BotbleVariables.languages || {};
        BotbleVariables.languages.reports = {!! json_encode(trans('plugins/ecommerce::reports.ranges'), JSON_HEX_APOS) !!}
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        'use strict';

        function initStoreQRCode() {
            var qrcodeEl = document.getElementById('store-qrcode');
            if (!qrcodeEl || qrcodeEl.childNodes.length > 0) return;

            var storeUrl = qrcodeEl.getAttribute('data-url');
            if (!storeUrl) return;

            new QRCode(qrcodeEl, {
                text: storeUrl,
                width: 180,
                height: 180,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        document.addEventListener('DOMContentLoaded', initStoreQRCode);

        $(document).ajaxComplete(function() {
            setTimeout(initStoreQRCode, 200);
        });

        $(document).on('click', '#download-qrcode', function() {
            var canvas = document.querySelector('#store-qrcode canvas');
            if (canvas) {
                var link = document.createElement('a');
                link.download = 'store-qrcode.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }
        });

        $(document).on('click', '#copy-store-link', function() {
            var url = $(this).data('url');
            var btn = $(this);
            navigator.clipboard.writeText(url).then(function() {
                var originalHtml = btn.html();
                btn.html('<i class="ti ti-check"></i> {{ __("Copied!") }}');
                setTimeout(function() {
                    btn.html(originalHtml);
                }, 2000);
            });
        });
    </script>
@endpush
