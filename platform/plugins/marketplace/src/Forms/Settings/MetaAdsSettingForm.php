<?php

namespace Botble\Marketplace\Forms\Settings;

use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
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
            ->setSectionTitle('Meta Ads Integration')
            ->setSectionDescription('Configure Facebook Authentication and Marketing API credentials.')
            ->setValidatorClass(MetaAdsSettingRequest::class)
            ->contentOnly()
            ->add('meta_ads_enabled', OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label('Enable Meta Ads Module')
                    ->helperText('Turn on/off Meta Ads for vendors.')
                    ->value(MarketplaceHelper::getSetting('meta_ads_enabled', false))
            )
            ->add('fb_auth_section', 'html', ['html' => '<hr><h5>Facebook Auth App (Facebook Login)</h5><p class="text-muted small">Used as a fallback for OAuth if Marketing App is not configured. Must have "Facebook Login" product added.</p>'])
            ->add('meta_ads_fb_auth_app_id', TextField::class,
                TextFieldOption::make()->label('Auth App ID')
                    ->value(MarketplaceHelper::getSetting('meta_ads_fb_auth_app_id', ''))
                    ->placeholder('123456789012345')
            )
            ->add('meta_ads_fb_auth_app_secret', TextField::class,
                TextFieldOption::make()->label('Auth App Secret')
                    ->value(MarketplaceHelper::getSetting('meta_ads_fb_auth_app_secret', ''))
            )
            ->add('meta_ads_fb_auth_redirect_uri', TextField::class,
                TextFieldOption::make()->label('OAuth Redirect URI')
                    ->helperText('Add this in Facebook App → Facebook Login → Valid OAuth Redirect URIs')
                    ->value(MarketplaceHelper::getSetting('meta_ads_fb_auth_redirect_uri', url('/vendor/meta-ads/callback')))
            )
            ->add('marketing_api_section', 'html', ['html' => '<hr><h5>Marketing App (Primary — Recommended)</h5><p class="text-muted small">If configured, this app is used for both OAuth AND all Marketing API calls. Must have "Marketing API" + "Facebook Login" products added. Leave empty to use Auth App above.</p>'])
            ->add('meta_ads_marketing_app_id', TextField::class,
                TextFieldOption::make()->label('Marketing App ID')
                    ->helperText('App ID of your Marketing API app (preferred for ads OAuth)')
                    ->value(MarketplaceHelper::getSetting('meta_ads_marketing_app_id', ''))
            )
            ->add('meta_ads_marketing_app_secret', TextField::class,
                TextFieldOption::make()->label('Marketing App Secret')
                    ->value(MarketplaceHelper::getSetting('meta_ads_marketing_app_secret', ''))
            )
            ->add('meta_ads_marketing_developer_token', TextField::class,
                TextFieldOption::make()->label('Developer Token (System User Token)')
                    ->helperText('Optional: System User access token from Meta Business Manager for server-side calls.')
                    ->value(MarketplaceHelper::getSetting('meta_ads_marketing_developer_token', ''))
            )
            ->add('advanced_section', 'html', ['html' => '<hr><h5>Advanced</h5>'])
            ->add('meta_ads_sandbox_mode', OnOffCheckboxField::class,
                OnOffFieldOption::make()->label('Sandbox Mode')
                    ->value(MarketplaceHelper::getSetting('meta_ads_sandbox_mode', true))
            )
            ->add('meta_ads_api_version', TextField::class,
                TextFieldOption::make()->label('API Version')
                    ->value(MarketplaceHelper::getSetting('meta_ads_api_version', 'v21.0'))
                    ->placeholder('v21.0')
            );
    }
}
