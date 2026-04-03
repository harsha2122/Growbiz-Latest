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
            ->setSectionDescription('Configure Facebook Authentication and Marketing API credentials. Authentication keys are used for Facebook Login (vendor connects their account). Marketing API keys are used for managing ads, campaigns, and insights.')
            ->setValidatorClass(MetaAdsSettingRequest::class)
            ->contentOnly()

            // Master toggle
            ->add(
                'meta_ads_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label('Enable Meta Ads Module')
                    ->helperText('Turn on/off the entire Meta Ads module for vendors. When disabled, the Meta Ads menu will be hidden from vendor dashboard.')
                    ->value(MarketplaceHelper::getSetting('meta_ads_enabled', false))
            )

            // Section: Facebook Authentication
            ->add('fb_auth_section', 'html', [
                'html' => '<hr class="my-3"><h4 class="mb-1">Facebook Authentication (OAuth Login)</h4><p class="text-muted mb-3">These credentials are used when vendors click "Connect with Facebook" to link their account.</p>',
            ])

            ->add(
                'meta_ads_fb_auth_app_id',
                TextField::class,
                TextFieldOption::make()
                    ->label('Facebook Auth App ID')
                    ->helperText('The App ID from your Facebook Login app (developers.facebook.com)')
                    ->value(MarketplaceHelper::getSetting('meta_ads_fb_auth_app_id', ''))
                    ->placeholder('e.g. 123456789012345')
            )

            ->add(
                'meta_ads_fb_auth_app_secret',
                TextField::class,
                TextFieldOption::make()
                    ->label('Facebook Auth App Secret')
                    ->helperText('The App Secret from your Facebook Login app. Keep this confidential.')
                    ->value(MarketplaceHelper::getSetting('meta_ads_fb_auth_app_secret', ''))
                    ->placeholder('e.g. abc123def456...')
            )

            ->add(
                'meta_ads_fb_auth_redirect_uri',
                TextField::class,
                TextFieldOption::make()
                    ->label('OAuth Redirect URI')
                    ->helperText('Add this URL in your Facebook App → Facebook Login → Settings → Valid OAuth Redirect URIs')
                    ->value(MarketplaceHelper::getSetting('meta_ads_fb_auth_redirect_uri', url('/vendor/meta-ads/callback')))
                    ->placeholder(url('/vendor/meta-ads/callback'))
            )

            // Section: Marketing API
            ->add('marketing_api_section', 'html', [
                'html' => '<hr class="my-3"><h4 class="mb-1">Marketing API Credentials</h4><p class="text-muted mb-3">These credentials are used for managing ad campaigns, ad sets, ads, and reading insights via Meta Marketing API.</p>',
            ])

            ->add(
                'meta_ads_marketing_app_id',
                TextField::class,
                TextFieldOption::make()
                    ->label('Marketing API App ID')
                    ->helperText('The App ID that has Marketing API access approved')
                    ->value(MarketplaceHelper::getSetting('meta_ads_marketing_app_id', ''))
                    ->placeholder('e.g. 987654321098765')
            )

            ->add(
                'meta_ads_marketing_app_secret',
                TextField::class,
                TextFieldOption::make()
                    ->label('Marketing API App Secret')
                    ->helperText('The App Secret for the Marketing API app')
                    ->value(MarketplaceHelper::getSetting('meta_ads_marketing_app_secret', ''))
                    ->placeholder('e.g. xyz789abc012...')
            )

            ->add(
                'meta_ads_marketing_developer_token',
                TextField::class,
                TextFieldOption::make()
                    ->label('Developer Token')
                    ->helperText('Found in App Dashboard → Settings → Advanced → Marketing API → Developer Token')
                    ->value(MarketplaceHelper::getSetting('meta_ads_marketing_developer_token', ''))
                    ->placeholder('e.g. EAABsbCS1IHED...')
            )

            // Section: Advanced Settings
            ->add('advanced_section', 'html', [
                'html' => '<hr class="my-3"><h4 class="mb-1">Advanced Settings</h4>',
            ])

            ->add(
                'meta_ads_sandbox_mode',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label('Sandbox / Testing Mode')
                    ->helperText('When enabled, API calls will use sandbox endpoints. Useful for testing without spending real money.')
                    ->value(MarketplaceHelper::getSetting('meta_ads_sandbox_mode', true))
            )

            ->add(
                'meta_ads_api_version',
                TextField::class,
                TextFieldOption::make()
                    ->label('API Version')
                    ->helperText('Meta Marketing API version to use (e.g. v21.0)')
                    ->value(MarketplaceHelper::getSetting('meta_ads_api_version', 'v21.0'))
                    ->placeholder('v21.0')
            );
    }
}
