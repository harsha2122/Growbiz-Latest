<?php

use Botble\Marketplace\Http\Controllers\Fronts\MetaAdController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdSetController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdsConnectionController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaAdsDashboardController;
use Botble\Marketplace\Http\Controllers\Fronts\MetaCampaignController;
use Botble\Marketplace\Http\Middleware\LocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace'  => 'Botble\Marketplace\Http\Controllers\Fronts',
    'prefix'     => config('plugins.marketplace.general.vendor_panel_dir', 'vendor') . '/meta-ads',
    'as'         => 'marketplace.vendor.meta-ads.',
    'middleware' => ['web', 'core', 'vendor', LocaleMiddleware::class],
], function (): void {

    // ── Dashboard ──────────────────────────────────────────────────────────────
    Route::get('/', [MetaAdsDashboardController::class, 'index'])->name('dashboard');

    // ── Facebook Connection / OAuth ────────────────────────────────────────────
    Route::get('connection', [MetaAdsConnectionController::class, 'index'])->name('connection');
    Route::get('callback', [MetaAdsConnectionController::class, 'callback'])->name('callback');
    Route::post('connection/select-account', [MetaAdsConnectionController::class, 'selectAccount'])->name('connection.select-account');
    Route::post('connection/disconnect', [MetaAdsConnectionController::class, 'disconnect'])->name('connection.disconnect');

    // ── Campaigns ──────────────────────────────────────────────────────────────
    Route::get('campaigns', [MetaCampaignController::class, 'index'])->name('campaigns.index');
    Route::get('campaigns/create', [MetaCampaignController::class, 'create'])->name('campaigns.create');
    Route::post('campaigns', [MetaCampaignController::class, 'store'])->name('campaigns.store');
    Route::get('campaigns/{id}', [MetaCampaignController::class, 'show'])->name('campaigns.show');
    Route::get('campaigns/{id}/edit', [MetaCampaignController::class, 'edit'])->name('campaigns.edit');
    Route::put('campaigns/{id}', [MetaCampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('campaigns/{id}', [MetaCampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::post('campaigns/{id}/toggle-status', [MetaCampaignController::class, 'toggleStatus'])->name('campaigns.toggle-status');
    Route::post('campaigns/{id}/push-to-meta', [MetaCampaignController::class, 'pushToMeta'])->name('campaigns.push-to-meta');

    // ── Ad Sets — create/store nested under campaign ───────────────────────────
    Route::get('campaigns/{campaignId}/ad-sets/create', [MetaAdSetController::class, 'create'])->name('campaigns.ad-sets.create');
    Route::post('campaigns/{campaignId}/ad-sets', [MetaAdSetController::class, 'store'])->name('campaigns.ad-sets.store');
    // Static routes must precede wildcard {id} routes
    Route::get('ad-sets/search-locations', [MetaAdSetController::class, 'searchLocations'])->name('ad-sets.search-locations');
    Route::get('ad-sets/{id}', [MetaAdSetController::class, 'show'])->name('ad-sets.show');
    Route::get('ad-sets/{id}/edit', [MetaAdSetController::class, 'edit'])->name('ad-sets.edit');
    Route::put('ad-sets/{id}', [MetaAdSetController::class, 'update'])->name('ad-sets.update');
    Route::delete('ad-sets/{id}', [MetaAdSetController::class, 'destroy'])->name('ad-sets.destroy');
    Route::post('ad-sets/{id}/toggle-status', [MetaAdSetController::class, 'toggleStatus'])->name('ad-sets.toggle-status');
    Route::post('ad-sets/{id}/push-to-meta', [MetaAdSetController::class, 'pushToMeta'])->name('ad-sets.push-to-meta');

    // ── Ads — create/store nested under ad set ─────────────────────────────────
    Route::get('ad-sets/{adSetId}/ads/create', [MetaAdController::class, 'create'])->name('ad-sets.ads.create');
    Route::post('ad-sets/{adSetId}/ads', [MetaAdController::class, 'store'])->name('ad-sets.ads.store');
    Route::get('ads/{id}', [MetaAdController::class, 'show'])->name('ads.show');
    Route::get('ads/{id}/preview', [MetaAdController::class, 'preview'])->name('ads.preview');
    Route::get('ads/{id}/edit', [MetaAdController::class, 'edit'])->name('ads.edit');
    Route::put('ads/{id}', [MetaAdController::class, 'update'])->name('ads.update');
    Route::delete('ads/{id}', [MetaAdController::class, 'destroy'])->name('ads.destroy');
    Route::post('ads/{id}/toggle-status', [MetaAdController::class, 'toggleStatus'])->name('ads.toggle-status');
    Route::post('ads/{id}/push-to-meta', [MetaAdController::class, 'pushToMeta'])->name('ads.push-to-meta');
});
