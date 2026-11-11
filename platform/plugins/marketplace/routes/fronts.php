<?php

use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\Marketplace\Http\Controllers\Fronts\BecomeVendorController;
use Botble\Marketplace\Http\Controllers\Fronts\ContactStoreController;
use Botble\Marketplace\Http\Controllers\Fronts\EnquiryController;
use Botble\Marketplace\Http\Controllers\Fronts\PublicB2bCatalogController;
use Botble\Marketplace\Http\Controllers\Fronts\PublicStoreController;
use Botble\Marketplace\Http\Controllers\Fronts\ServiceBookingController;
use Botble\Marketplace\Http\Controllers\Fronts\SponsoredVideoClickController;
use Botble\Marketplace\Http\Controllers\Fronts\WhatsAppEnquiryController;
use Botble\Marketplace\Models\Store;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Botble\Marketplace\Http\Controllers\Fronts',
], function (): void {
    Route::group(['middleware' => ['web', 'core']], function () {
        Route::post('ajax/stores/check-store-url', [PublicStoreController::class, 'checkStoreUrl'])
            ->name('public.ajax.check-store-url');
    });

    Theme::registerRoutes(function (): void {
        Route::post('stores/{store}/profile-rating-intent', [\Botble\Marketplace\Http\Controllers\Fronts\ProfileReviewController::class, 'start'])->whereNumber('store')->middleware('throttle:20,1')->name('public.store.profile-rating-intent');
        Route::middleware(['customer', 'throttle:60,1'])->group(function (): void {
            Route::get('customer/activity-notifications', [\Botble\Marketplace\Http\Controllers\Fronts\ActivityNotificationController::class, 'index'])->name('public.activity-notifications.index');
            Route::post('customer/activity-notifications/read', [\Botble\Marketplace\Http\Controllers\Fronts\ActivityNotificationController::class, 'read'])->name('public.activity-notifications.read');
            Route::post('customer/activity-notifications/subscribe', [\Botble\Marketplace\Http\Controllers\Fronts\ActivityNotificationController::class, 'subscribe'])->name('public.activity-notifications.subscribe');
            Route::post('customer/activity-notifications/unsubscribe', [\Botble\Marketplace\Http\Controllers\Fronts\ActivityNotificationController::class, 'unsubscribe'])->name('public.activity-notifications.unsubscribe');
        });
        Route::get('referral-offers/{program}/terms', [\Botble\Marketplace\Http\Controllers\Fronts\ReferralActivityController::class, 'terms'])->whereNumber('program')->name('public.referral.terms');
        Route::get('refer/{token}/qr', [\Botble\Marketplace\Http\Controllers\Fronts\ReferralActivityController::class, 'qr'])->middleware('throttle:60,1')->name('public.referral.qr');
        Route::get('refer/{token}', [\Botble\Marketplace\Http\Controllers\Fronts\StoreReferralController::class, 'landing'])->middleware('throttle:60,1')->name('public.referral.landing');
        Route::middleware(['customer', 'throttle:30,1'])->group(function (): void {
            Route::get('customer/referral-pass/{code}', [\Botble\Marketplace\Http\Controllers\Fronts\ReferralActivityController::class, 'pass'])->name('public.referral.pass');
            Route::post('customer/referral-activities/{event}/confirm', [\Botble\Marketplace\Http\Controllers\Fronts\ReferralActivityController::class, 'confirm'])->whereNumber('event')->name('public.referral.confirm');
            Route::get('customer/referral-activities/{event}/receipt', [\Botble\Marketplace\Http\Controllers\Fronts\ReferralActivityController::class, 'evidence'])->whereNumber('event')->name('public.referral.evidence');
            Route::post('stores/{store}/profile-review', [\Botble\Marketplace\Http\Controllers\Fronts\ProfileReviewController::class, 'store'])->whereNumber('store')->name('public.store.profile-review');
            Route::post('customer/refer-earn/{referral}/request-payment', [\Botble\Marketplace\Http\Controllers\Fronts\StoreReferralController::class, 'requestPayment'])->name('public.referral.request-payment');
            Route::get('customer/refer-earn', [\Botble\Marketplace\Http\Controllers\Fronts\StoreReferralController::class, 'account'])->name('public.referral.account');
            Route::post('stores/{store}/refer-earn/join', [\Botble\Marketplace\Http\Controllers\Fronts\StoreReferralController::class, 'join'])->name('public.referral.join');
            Route::post('stores/{store}/refer-earn/claim', [\Botble\Marketplace\Http\Controllers\Fronts\StoreReferralController::class, 'claim'])->name('public.referral.claim');
        });

        $slugPrefix = SlugHelper::getPrefix(Store::class, 'stores');

        Route::get($slugPrefix, [PublicStoreController::class, 'getStores'])->name('public.stores');
        Route::get("$slugPrefix/{slug}", [PublicStoreController::class, 'getStore'])->name('public.store');

        Route::prefix('ajax/stores')
            ->name('public.ajax.')
            ->middleware(RequiresJsonRequestMiddleware::class)
            ->group(function (): void {
                Route::post('{id}/contact', [ContactStoreController::class, 'store'])->name('stores.contact');
            });

        Route::prefix('ajax/enquiries')
            ->name('public.ajax.enquiries.')
            ->middleware(RequiresJsonRequestMiddleware::class)
            ->group(function (): void {
                Route::post('', [EnquiryController::class, 'store'])->name('store');
            });

        Route::prefix('ajax/whatsapp-enquiries')
            ->name('public.ajax.whatsapp-enquiries.')
            ->middleware(RequiresJsonRequestMiddleware::class)
            ->group(function (): void {
                Route::post('', [WhatsAppEnquiryController::class, 'store'])->name('store');
            });

        Route::prefix('ajax/sponsored-videos')
            ->name('public.ajax.sponsored-videos.')
            ->middleware(RequiresJsonRequestMiddleware::class)
            ->group(function (): void {
                Route::post('{id}/click', [SponsoredVideoClickController::class, 'store'])->name('click');
            });

        Route::get('products/{product}/book-now', [ServiceBookingController::class, 'book'])
            ->name('public.products.book-now');

        Route::prefix('b2b-catalogs')->name('public.b2b-catalogs.')->group(function (): void {
            Route::get('/', [PublicB2bCatalogController::class, 'index'])->name('index');
            Route::get('{catalog}/pdfs/{pdf}/view', [PublicB2bCatalogController::class, 'viewPdf'])->name('pdf.view');
            Route::get('{catalog}/pdfs/{pdf}/stream', [PublicB2bCatalogController::class, 'streamPdf'])->name('pdf.stream');
        });

        Route::middleware('customer')->prefix('customer/become-vendor')->name('marketplace.vendor.')->group(
            function (): void {
                Route::get('/', [BecomeVendorController::class, 'index'])->name('become-vendor');
                Route::post('/', [BecomeVendorController::class, 'store'])->name('become-vendor.post');
                Route::put('/', [BecomeVendorController::class, 'update'])->name('become-vendor.update');
                Route::get('download', [BecomeVendorController::class, 'downloadDocument'])->name('become-vendor.download');
            }
        );
    });
});

