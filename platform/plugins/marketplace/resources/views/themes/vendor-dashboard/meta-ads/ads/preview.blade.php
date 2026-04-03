@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $ad->adSet->campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $ad->adSet->campaign_id)],
        ['label' => $ad->name, 'url' => route('marketplace.vendor.meta-ads.ads.show', $ad->id)],
        ['label' => 'Preview'],
    ]])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Ad Preview') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.ads.show', $ad->id) }}" class="btn btn-outline-secondary">
            <x-core::icon name="ti ti-arrow-left" /> {{ __('Back') }}
        </a>
    </div>

    <div class="row g-4">
        {{-- Facebook Feed Preview --}}
        <div class="col-md-6">
            <h6 class="text-center text-muted mb-3"><x-core::icon name="ti ti-brand-facebook" /> {{ __('Facebook Feed') }}</h6>
            <div style="max-width: 500px; margin: 0 auto; background: #f0f2f5; padding: 16px; border-radius: 8px;">
                <div class="bg-white rounded-3 shadow-sm overflow-hidden">
                    {{-- Post Header --}}
                    <div class="d-flex align-items-center p-3 pb-2">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px; font-size: 16px;">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                        <div class="ms-2">
                            <strong style="font-size: 14px;">{{ $storeName }}</strong>
                            <div style="font-size: 12px; color: #65676B;">
                                Sponsored · <x-core::icon name="ti ti-world" style="font-size: 11px;" />
                            </div>
                        </div>
                        <div class="ms-auto">
                            <x-core::icon name="ti ti-dots" style="color: #65676B;" />
                        </div>
                    </div>

                    {{-- Primary Text --}}
                    <div class="px-3 pb-2" style="font-size: 15px; line-height: 1.4;">
                        {{ $ad->primary_text }}
                    </div>

                    {{-- Image --}}
                    @if ($ad->image_url)
                        <div style="background: #e4e6eb;">
                            <img src="{{ $ad->image_url }}" class="w-100 d-block" alt="" style="max-height: 500px; object-fit: cover;" onerror="this.parentElement.innerHTML='<div class=\'text-center py-5\' style=\'color:#bcc0c4\'><i class=\'ti ti-photo\' style=\'font-size:48px\'></i></div>'">
                        </div>
                    @else
                        <div class="text-center py-5" style="background: #e4e6eb; color: #bcc0c4;">
                            <x-core::icon name="ti ti-photo" style="font-size: 48px;" />
                        </div>
                    @endif

                    {{-- CTA Section --}}
                    <div class="p-3 d-flex justify-content-between align-items-center" style="background: #f0f2f5;">
                        <div style="min-width: 0; flex: 1;">
                            @if ($ad->destination_url)
                                <div style="font-size: 12px; color: #65676B; text-transform: uppercase;">{{ parse_url($ad->destination_url, PHP_URL_HOST) }}</div>
                            @endif
                            <div style="font-size: 15px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $ad->headline }}</div>
                            @if ($ad->description)
                                <div style="font-size: 13px; color: #65676B; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $ad->description }}</div>
                            @endif
                        </div>
                        <button class="btn btn-sm ms-2" style="background: #e4e6eb; font-weight: 600; white-space: nowrap;">
                            {{ str_replace('_', ' ', $ad->cta_button) }}
                        </button>
                    </div>

                    {{-- Reactions Bar --}}
                    <div class="px-3 py-2 d-flex justify-content-between border-top" style="font-size: 13px; color: #65676B;">
                        <span>👍 ❤️ 42</span>
                        <span>8 Comments · 3 Shares</span>
                    </div>
                    <div class="px-3 py-2 d-flex justify-content-around border-top" style="font-size: 14px; color: #65676B; font-weight: 600;">
                        <span><x-core::icon name="ti ti-thumb-up" /> Like</span>
                        <span><x-core::icon name="ti ti-message-circle" /> Comment</span>
                        <span><x-core::icon name="ti ti-share" /> Share</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Instagram Feed Preview --}}
        <div class="col-md-6">
            <h6 class="text-center text-muted mb-3"><x-core::icon name="ti ti-brand-instagram" /> {{ __('Instagram Feed') }}</h6>
            <div style="max-width: 400px; margin: 0 auto; background: #000; padding: 0; border-radius: 8px; overflow: hidden;">
                <div style="background: #fff;">
                    {{-- IG Header --}}
                    <div class="d-flex align-items-center p-2">
                        <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; font-size: 12px; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                        <div class="ms-2">
                            <strong style="font-size: 13px;">{{ Str::slug($storeName) }}</strong>
                            <div style="font-size: 11px; color: #8e8e8e;">Sponsored</div>
                        </div>
                        <div class="ms-auto"><x-core::icon name="ti ti-dots" style="color: #262626;" /></div>
                    </div>

                    {{-- IG Image --}}
                    @if ($ad->image_url)
                        <img src="{{ $ad->image_url }}" class="w-100 d-block" alt="" style="aspect-ratio: 1; object-fit: cover;" onerror="this.style.background='#efefef'">
                    @else
                        <div class="text-center d-flex align-items-center justify-content-center" style="aspect-ratio: 1; background: #efefef; color: #bbb;">
                            <x-core::icon name="ti ti-photo" style="font-size: 48px;" />
                        </div>
                    @endif

                    {{-- IG Actions --}}
                    <div class="d-flex align-items-center p-2 gap-3">
                        <x-core::icon name="ti ti-heart" style="font-size: 24px;" />
                        <x-core::icon name="ti ti-message-circle" style="font-size: 24px;" />
                        <x-core::icon name="ti ti-send" style="font-size: 24px;" />
                        <div class="ms-auto"><x-core::icon name="ti ti-bookmark" style="font-size: 24px;" /></div>
                    </div>

                    {{-- IG CTA Button --}}
                    <div class="px-2 pb-2">
                        <a href="#" class="btn btn-sm btn-outline-dark w-100" style="font-weight: 600;">
                            {{ str_replace('_', ' ', $ad->cta_button) }}
                        </a>
                    </div>

                    {{-- IG Caption --}}
                    <div class="px-2 pb-2" style="font-size: 13px;">
                        <strong>{{ Str::slug($storeName) }}</strong> {{ Str::limit($ad->primary_text, 100) }}
                    </div>
                    <div class="px-2 pb-3" style="font-size: 11px; color: #8e8e8e;">2 HOURS AGO</div>
                </div>
            </div>
        </div>
    </div>
@stop
