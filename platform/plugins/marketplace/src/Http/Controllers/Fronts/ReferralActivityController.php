<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\ReferralEvent;
use Botble\Marketplace\Models\ReferralLink;
use Botble\Marketplace\Models\ReferralProgram;
use Botble\Marketplace\Services\ReferralVerificationService;
use Botble\Theme\Facades\Theme;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReferralActivityController extends BaseController
{
    private function svg(string $url): string
    {
        return (new Writer(new ImageRenderer(new RendererStyle(240), new SvgImageBackEnd)))->writeString($url);
    }

    public function qr(string $token)
    {
        $link = ReferralLink::query()->where('token', $token)->firstOrFail();
        abort_unless($link->program->isActive(), 404);

        return response($this->svg(route('public.referral.landing', $token)))->header('Content-Type', 'image/svg+xml')->header('X-Content-Type-Options', 'nosniff');
    }

    public function pass(string $code)
    {
        $referral = CustomerReferral::query()->where('pass_code', $code)->with(['store', 'referrer'])->firstOrFail();
        $customer = auth('customer')->user();
        if ($customer->store?->id == $referral->store_id && $customer->id != $referral->customer_id) {
            return redirect()->route('marketplace.vendor.referral-activity', ['code' => $code]);
        }
        abort_unless($referral->customer_id == $customer->id, 404);
        $version = EcommerceHelper::getAssetVersion();
        Theme::asset()->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);
        $svg = $this->svg(route('public.referral.pass', $code));
        $events = ReferralEvent::query()->where('referral_id', $referral->id)->latest()->get();

        return Theme::scope('marketplace.referral-pass', compact('referral', 'svg', 'events'), 'plugins/marketplace::referral-program.pass')->render();
    }

    public function seller(Request $request)
    {
        $store = auth('customer')->user()->store;
        abort_unless($store?->exists, 404);
        $this->pageTitle(__('Offline referrals'));
        $events = ReferralEvent::query()->where('store_id', $store->id)->with(['referral.customer', 'referral.referrer'])->latest()->paginate(20);

        return MarketplaceHelper::view('vendor-dashboard.referral-activity', ['events' => $events, 'code' => $request->query('code', '')]);
    }

    public function submit(Request $request, ReferralVerificationService $service)
    {
        $store = auth('customer')->user()->store;
        abort_unless($store?->exists, 404);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'], 'customer_email' => ['nullable', 'email', 'max:191'],
            'reference' => ['required', 'string', 'max:191'], 'occurred_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'subtotal' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
            'discount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
            'tax' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
            'shipping' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        $path = null;
        try {
            if ($request->hasFile('receipt')) {
                $data['receipt_hash'] = hash_file('sha256', $request->file('receipt')->getRealPath());
                $path = $request->file('receipt')->store('referral-evidence/'.$store->id, 'local');
                $data['evidence_path'] = $path;
            }
            $service->offline($store->id, auth('customer')->id(), $data);
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            if ($exception instanceof QueryException && in_array((string) $exception->getCode(), ['23000', '23505'])) {
                throw ValidationException::withMessages(['reference' => __('This receipt or invoice has already been submitted for this store.')]);
            }
            throw $exception;
        }

        return back()->with('success_msg', __('Activity recorded. Ask the customer to confirm it under My Account → Refer & Earn. Admin approval is required before rewards qualify.'));
    }

    public function confirm(int $event, Request $request, ReferralVerificationService $service)
    {
        $data = $request->validate(['accept' => ['required', 'boolean']]);
        $service->confirm($event, auth('customer')->id(), (bool) $data['accept']);

        return back()->with('success_msg', $data['accept'] ? __('Activity confirmed. Awaiting admin verification.') : __('Activity declined.'));
    }

    public function evidence(int $event)
    {
        $record = ReferralEvent::with('referral')->findOrFail($event);
        $customer = auth('customer')->user();
        $admin = auth()->check() && auth()->user()->hasPermission('marketplace.refer-earn.index');
        abort_unless($admin || ($customer && ($customer->id == $record->referral->customer_id || $customer->store?->id == $record->store_id)), 404);
        abort_unless($record->evidence_path && Storage::disk('local')->exists($record->evidence_path), 404);

        return Storage::disk('local')->download($record->evidence_path, 'referral-receipt-'.$record->id.'.'.pathinfo($record->evidence_path, PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function terms(int $program, Request $request)
    {
        $program = ReferralProgram::with('store')->findOrFail($program);
        $referral = null;
        if ($request->filled('referral')) {
            $id = auth('customer')->id();
            abort_unless($id, 404);
            $referral = CustomerReferral::query()->where('program_id', $program->id)->whereKey($request->input('referral'))
                ->where(fn ($q) => $q->where('customer_id', $id)->orWhere('referrer_id', $id))->firstOrFail();
        } else {
            abort_unless($program->isActive(), 404);
        }
        $terms = $referral?->terms ?? $program->snapshot();

        return response()->view('plugins/marketplace::referral-program.terms', compact('program', 'terms', 'referral'))->header('Cache-Control', 'private, no-store');
    }
}
