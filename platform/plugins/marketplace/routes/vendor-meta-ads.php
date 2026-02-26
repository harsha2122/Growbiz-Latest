<?php

use Botble\Marketplace\Http\Controllers\Fronts\MetaAdAccountController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdSetController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaCampaignController;
use Botble\Marketplace\Http\Middleware\LocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Botble\Marketplace\Http\Controllers\Fronts',
    'prefix' => config('plugins.marketplace.general.vendor_panel_dir', 'vendor') . '/meta-ads',
    'as' => 'marketplace.vendor.meta-ads.',
    'middleware' => ['web', 'core', 'vendor', LocaleMiddleware::class],
], function (): void {

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
});
