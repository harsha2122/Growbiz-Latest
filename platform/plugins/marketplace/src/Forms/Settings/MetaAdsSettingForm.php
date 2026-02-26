<?php

namespace Botble\Marketplace\Forms\Settings;

use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\MetaAdsSettingRequest;
use Botble\Setting\Forms\SettingForm;

class MetaAdsSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(__('Meta Ads Settings'))
            ->setSectionDescription(__('Configure your Facebook / Meta Ads integration.'))
            ->setValidatorClass(MetaAdsSettingRequest::class)
            ->contentOnly()
            ->add(
                'meta_ads_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(__('Enable Meta Ads for Vendors'))
                    ->helperText(__('Allow vendors to create and manage Meta (Facebook/Instagram) ad campaigns from their dashboard.'))
                    ->value(MarketplaceHelper::isMetaAdsEnabled())
            )
            ->add(
                'meta_app_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Facebook App ID'))
                    ->helperText(__('Your Facebook App ID from the Meta Developer Portal.'))
                    ->value(MarketplaceHelper::getMetaAppId())
                    ->placeholder('123456789012345')
            )
            ->add(
                'meta_app_secret',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Facebook App Secret'))
                    ->helperText(__('Your Facebook App Secret. This is sensitive — treat it like a password.'))
                    ->value(MarketplaceHelper::getMetaAppSecret())
                    ->placeholder(__('Leave blank to keep existing value'))
            )
            ->add(
                'meta_api_version',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Meta API Version'))
                    ->helperText(__('The Meta Graph API version to use (e.g. v19.0).'))
                    ->value(MarketplaceHelper::getMetaApiVersion())
                    ->placeholder('v19.0')
            )
            ->add(
                'meta_ads_min_daily_budget',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(__('Minimum Daily Budget ($)'))
                    ->helperText(__('The minimum daily budget a vendor must set when creating an ad campaign.'))
                    ->value(MarketplaceHelper::getMetaAdsMinDailyBudget())
            )
            ->add(
                'meta_ads_default_currency',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Default Ad Currency'))
                    ->helperText(__('ISO 4217 currency code for ad spend (e.g. USD, EUR).'))
                    ->value(MarketplaceHelper::getMetaAdsDefaultCurrency())
                    ->placeholder('USD')
            );
    }
}
