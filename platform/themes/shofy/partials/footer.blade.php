{!! apply_filters('ads_render', null, 'footer_before') !!}

{!! Theme::partial('footer.style-1') !!}

{!! apply_filters('ads_render', null, 'footer_after') !!}

@if (is_plugin_active('marketplace') && auth('customer')->check())
    @include('plugins/marketplace::partials.activity-notifications', ['notificationAudience' => 'customer'])
@endif
