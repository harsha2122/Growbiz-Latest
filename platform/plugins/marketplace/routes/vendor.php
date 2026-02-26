<?php

use Botble\Base\Http\Middleware\DisableInDemoModeMiddleware;
use Botble\DataSynchronize\Http\Controllers\UploadController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Controllers\PrintShippingLabelController;
use Botble\Ecommerce\Http\Controllers\ProductTagController;
use Botble\Ecommerce\Http\Middleware\CheckProductSpecificationEnabledMiddleware;
use Botble\Marketplace\Http\Controllers\Fronts\ExportProductController;
use Botble\Marketplace\Http\Controllers\Fronts\ImportProductController;
use Botble\Marketplace\Http\Controllers\Fronts\MessageController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdAccountController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdReportController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdSetController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaCampaignController;
use Botble\Marketplace\Http\Controllers\Fronts\SpecificationAttributeController;
use Botble\Marketplace\Http\Controllers\Fronts\SpecificationGroupController;
use Botble\Marketplace\Http\Controllers\Fronts\SpecificationTableController;
use Botble\Marketplace\Http\Controllers\Vendor\LanguageSettingController;
use Botble\Marketplace\Http\Middleware\LocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Botble\Marketplace\Http\Controllers\Fronts',
    'prefix' => config('plugins.marketplace.general.vendor_panel_dir', 'vendor'),
    'as' => 'marketplace.vendor.',
    'middleware' => ['web', 'core', 'vendor', LocaleMiddleware::class],
], function (): void {
    Route::get('tags/all', [ProductTagController::class, 'getAllTags'])->name('tags.all');

    require core_path('table/routes/web-actions.php');

    Route::group(['prefix' => 'ajax'], function (): void {
        Route::post('upload', [
            'as' => 'upload',
            'uses' => 'DashboardController@postUpload',
        ]);

        Route::post('upload-from-editor', [
            'as' => 'upload-from-editor',
            'uses' => 'DashboardController@postUploadFromEditor',
        ]);

        Route::group(['prefix' => 'chart', 'as' => 'chart.'], function (): void {
            Route::get('month', [
                'as' => 'month',
                'uses' => 'RevenueController@getMonthChart',
            ]);
        });
    });

    Route::get('dashboard', [
        'as' => 'dashboard',
        'uses' => 'DashboardController@index',
    ]);

    Route::get('settings', [
        'as' => 'settings',
        'uses' => 'SettingController@index',
    ]);

    Route::post('settings', [
        'as' => 'settings.post',
        'uses' => 'SettingController@saveSettings',
    ]);

    Route::post('settings/tax-info', [
        'as' => 'settings.post.tax-info',
        'uses' => 'SettingController@updateTaxInformation',
    ]);

    Route::post('settings/payout', [
        'as' => 'settings.post.payout',
        'uses' => 'SettingController@updatePayoutInformation',
    ]);

    Route::resource('revenues', 'RevenueController')
        ->parameters(['' => 'revenue'])
        ->only(['index']);

    Route::get('statements', fn () => to_route('marketplace.vendor.revenues.index'))
        ->name('statements.index');

    Route::resource('withdrawals', 'WithdrawalController')
        ->parameters(['' => 'withdrawal'])
        ->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
        ]);

    Route::group(['prefix' => 'withdrawals'], function (): void {
        Route::get('show/{id}', [
            'as' => 'withdrawals.show',
            'uses' => 'WithdrawalController@show',
        ])->wherePrimaryKey();
    });

    Route::match(['GET', 'POST'], 'messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    if (EcommerceHelper::isReviewEnabled()) {
        Route::resource('reviews', 'ReviewController')
            ->parameters(['' => 'review'])
            ->only(['index']);
    }

    Route::group(['prefix' => 'products', 'as' => 'products.'], function (): void {
        Route::resource('', 'ProductController')
            ->parameters(['' => 'product']);

        Route::post('add-attribute-to-product/{id}', [
            'as' => 'add-attribute-to-product',
            'uses' => 'ProductController@postAddAttributeToProduct',
        ])->wherePrimaryKey();

        Route::post('delete-version/{id}', [
            'as' => 'delete-version',
            'uses' => 'ProductController@deleteVersion',
        ])->wherePrimaryKey();

        Route::delete('items/delete-versions', [
            'as' => 'delete-versions',
            'uses' => 'ProductController@deleteVersions',
        ]);

        Route::post('add-version/{id}', [
            'as' => 'add-version',
            'uses' => 'ProductController@postAddVersion',
        ])->wherePrimaryKey();

        Route::get('get-version-form/{id?}', [
            'as' => 'get-version-form',
            'uses' => 'ProductController@getVersionForm',
        ]);

        Route::post('update-version/{id}', [
            'as' => 'update-version',
            'uses' => 'ProductController@postUpdateVersion',
        ])->wherePrimaryKey();

        Route::post('generate-all-version/{id}', [
            'as' => 'generate-all-versions',
            'uses' => 'ProductController@postGenerateAllVersions',
        ])->wherePrimaryKey();

        Route::post('store-related-attributes/{id}', [
            'as' => 'store-related-attributes',
            'uses' => 'ProductController@postStoreRelatedAttributes',
        ]);

        Route::post('save-all-version/{id}', [
            'as' => 'save-all-versions',
            'uses' => 'ProductController@postSaveAllVersions',
        ])->wherePrimaryKey();

        Route::get('get-list-product-for-search', [
            'as' => 'get-list-product-for-search',
            'uses' => 'ProductController@getListProductForSearch',
        ]);

        Route::get('get-relations-box/{id?}', [
            'as' => 'get-relations-boxes',
            'uses' => 'ProductController@getRelationBoxes',
        ]);

        Route::get('get-list-products-for-select', [
            'as' => 'get-list-products-for-select',
            'uses' => 'ProductController@getListProductForSelect',
        ]);

        Route::post('create-product-when-creating-order', [
            'as' => 'create-product-when-creating-order',
            'uses' => 'ProductController@postCreateProductWhenCreatingOrder',
        ]);

        Route::get('get-all-products-and-variations', [
            'as' => 'get-all-products-and-variations',
            'uses' => 'ProductController@getAllProductAndVariations',
        ]);

        Route::post('update-order-by', [
            'as' => 'update-order-by',
            'uses' => 'ProductController@postUpdateOrderby',
        ]);

        Route::post('product-variations/{id}', [
            'as' => 'product-variations',
            'uses' => 'ProductController@getProductVariations',
        ])->wherePrimaryKey();

        Route::get('product-attribute-sets/{id?}', [
            'as' => 'product-attribute-sets',
            'uses' => 'ProductController@getProductAttributeSets',
        ])->wherePrimaryKey();

        Route::post('set-default-product-variation/{id}', [
            'as' => 'set-default-product-variation',
            'uses' => 'ProductController@setDefaultProductVariation',
        ])->wherePrimaryKey();
    });

    Route::group(['prefix' => 'orders', 'as' => 'orders.'], function (): void {
        Route::resource('', 'OrderController')->parameters(['' => 'order'])->except(['create', 'store']);

        Route::get('generate-invoice/{id}', [
            'as' => 'generate-invoice',
            'uses' => 'OrderController@getGenerateInvoice',
        ])->wherePrimaryKey();

        Route::post('confirm', [
            'as' => 'confirm',
            'uses' => 'OrderController@postConfirm',
        ]);

        Route::post('send-order-confirmation-email/{id}', [
            'as' => 'send-order-confirmation-email',
            'uses' => 'OrderController@postResendOrderConfirmationEmail',
        ])->wherePrimaryKey();

        Route::post('update-shipping-address/{id}', [
            'as' => 'update-shipping-address',
            'uses' => 'OrderController@postUpdateShippingAddress',
        ])->wherePrimaryKey();

        Route::post('cancel-order/{id}', [
            'as' => 'cancel',
            'uses' => 'OrderController@postCancelOrder',
        ])->wherePrimaryKey();

        Route::post('confirm-payment/{id}', [
            'as' => 'confirm-payment',
            'uses' => 'OrderController@postConfirmPayment',
        ])->wherePrimaryKey();

        Route::post('update-shipping-status/{id}', [
            'as' => 'update-shipping-status',
            'uses' => 'ShipmentController@postUpdateStatus',
        ])->wherePrimaryKey();

        Route::get('download-proof/{order}', [
            'as' => 'download-proof',
            'uses' => 'OrderController@downloadProof',
        ]);
    });

    Route::group(['prefix' => 'order-returns', 'as' => 'order-returns.'], function (): void {
        Route::resource('', 'OrderReturnController')->parameters(['' => 'order'])->except(['create', 'store']);
    });

    Route::group(['prefix' => 'shipments', 'as' => 'shipments.'], function (): void {
        Route::resource('', 'ShipmentController')
            ->parameters(['' => 'shipment'])
            ->except(['create', 'store']);

        Route::post('update-cod-status/{id}', [
            'as' => 'update-cod-status',
            'uses' => 'ShipmentController@postUpdateCodStatus',
        ])->wherePrimaryKey();

        Route::get('shipments/{shipment}/print', [PrintShippingLabelController::class, '__invoke'])
            ->name('print');
    });

    Route::group(['prefix' => 'coupons', 'as' => 'discounts.'], function (): void {
        Route::resource('', 'DiscountController')->parameters(['' => 'discount'])->except(['edit', 'update']);

        Route::post('generate-coupon', [
            'as' => 'generate-coupon',
            'uses' => 'DiscountController@postGenerateCoupon',
        ]);
    });

    Route::get('ajax/product-options', [
        'as' => 'ajax-product-option-info',
        'uses' => 'ProductController@ajaxProductOptionInfo',
    ]);

    Route::prefix('export')->name('export.')->group(function (): void {
        Route::group(['prefix' => 'products', 'as' => 'products.'], function (): void {
            Route::get('/', [ExportProductController::class, 'index'])->name('index');
            Route::post('/', [ExportProductController::class, 'store'])->name('store');
        });
    });

    Route::prefix('import')->name('import.')->group(function (): void {
        Route::group(['prefix' => 'products', 'as' => 'products.'], function (): void {
            Route::get('/', [ImportProductController::class, 'index'])->name('index');
            Route::post('validate', [ImportProductController::class, 'validateData'])->name('validate');
            Route::post('import', [ImportProductController::class, 'import'])->name('store');
            Route::post('download-example', [ImportProductController::class, 'downloadExample'])->name(
                'download-example'
            );
        });

        Route::prefix('data-synchronize')->name('data-synchronize.')->group(function (): void {
            Route::post('upload', [UploadController::class, '__invoke'])
                ->middleware(DisableInDemoModeMiddleware::class)
                ->name('upload');
        });
    });

    Route::middleware([CheckProductSpecificationEnabledMiddleware::class])->group(function (): void {
        Route::prefix('specification-groups')->name('specification-groups.')->group(function (): void {
            Route::resource('/', SpecificationGroupController::class)->parameters(['' => 'group']);
        });
        Route::prefix('specification-attributes')->name('specification-attributes.')->group(function (): void {
            Route::resource('/', SpecificationAttributeController::class)->parameters(['' => 'attribute']);
        });
        Route::prefix('specification-tables')->name('specification-tables.')->group(function (): void {
            Route::resource('/', SpecificationTableController::class)->parameters(['' => 'table']);
        });
    });

    Route::group(['prefix' => 'b2b-catalogs', 'as' => 'b2b-catalogs.'], function (): void {
        Route::resource('', \Botble\Marketplace\Http\Controllers\Fronts\B2bCatalogController::class)
            ->parameters(['' => 'b2b_catalog']);
        Route::get('{b2b_catalog}/view-pdf', [
            \Botble\Marketplace\Http\Controllers\Fronts\B2bCatalogController::class, 'viewPdf',
        ])->name('view-pdf');
        Route::get('{b2b_catalog}/stream-pdf', [
            \Botble\Marketplace\Http\Controllers\Fronts\B2bCatalogController::class, 'streamPdf',
        ])->name('stream-pdf');
    });

    Route::get('settings/languages', [LanguageSettingController::class, 'index'])->name('language-settings.index');
    Route::put('settings/languages', [LanguageSettingController::class, 'update'])->name('language-settings.update');

    Route::prefix('contact-admin')->group(function (): void {
        Route::get('/', [
            'as' => 'contact-admin',
            'uses' => 'ContactAdminController@index',
        ]);

        Route::post('/', [
            'as' => 'contact-admin.store',
            'uses' => 'ContactAdminController@store',
        ]);
    });

    // Meta Ads
    Route::group(['prefix' => 'meta-ads', 'as' => 'meta-ads.'], function (): void {
        // Ad Account
        Route::get('accounts', [MetaAdAccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/connect', [MetaAdAccountController::class, 'connect'])->name('accounts.connect');
        Route::delete('accounts/{id}', [MetaAdAccountController::class, 'disconnect'])->name('accounts.disconnect');

        // Campaigns
        Route::get('campaigns', [MetaCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/create', [MetaCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [MetaCampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{id}/edit', [MetaCampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('campaigns/{id}', [MetaCampaignController::class, 'update'])->name('campaigns.update');
        Route::delete('campaigns/{id}', [MetaCampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::post('campaigns/{id}/pause', [MetaCampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{id}/resume', [MetaCampaignController::class, 'resume'])->name('campaigns.resume');

        // Ad Sets
        Route::get('ad-sets', [MetaAdSetController::class, 'index'])->name('ad-sets.index');
        Route::get('ad-sets/create', [MetaAdSetController::class, 'create'])->name('ad-sets.create');
        Route::post('ad-sets', [MetaAdSetController::class, 'store'])->name('ad-sets.store');
        Route::get('ad-sets/{id}/edit', [MetaAdSetController::class, 'edit'])->name('ad-sets.edit');
        Route::put('ad-sets/{id}', [MetaAdSetController::class, 'update'])->name('ad-sets.update');
        Route::delete('ad-sets/{id}', [MetaAdSetController::class, 'destroy'])->name('ad-sets.destroy');

        // Ads
        Route::get('ads', [MetaAdController::class, 'index'])->name('ads.index');
        Route::get('ads/create', [MetaAdController::class, 'create'])->name('ads.create');
        Route::post('ads', [MetaAdController::class, 'store'])->name('ads.store');
        Route::get('ads/{id}/edit', [MetaAdController::class, 'edit'])->name('ads.edit');
        Route::put('ads/{id}', [MetaAdController::class, 'update'])->name('ads.update');
        Route::delete('ads/{id}', [MetaAdController::class, 'destroy'])->name('ads.destroy');

        // Reports
        Route::get('reports', [MetaAdReportController::class, 'index'])->name('reports.index');
    });
});
