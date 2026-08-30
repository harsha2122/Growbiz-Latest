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
            ->add('fb_auth_section', 'html', ['html' => '<hr><h5>Facebook Auth App (Facebook Login)</h5><p class="text-muted small">Used as a fallback for OAuth if Marketing App is not configured. Must have "Facebook Login" product added. Also used to fetch real inline Instagram video embeds on product pages and sponsored store videos (via Meta\'s oEmbed API) - if the Marketing App below is configured, that one is used for this instead.</p>'])
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
            ->add('marketing_api_section', 'html', ['html' => '<hr><h5>Marketing App (Primary — Recommended)</h5><p class="text-muted small">If configured, this app is used for both OAuth AND all Marketing API calls, and takes priority over the Auth App above for Instagram video embeds too. Must have "Marketing API" + "Facebook Login" products added. Leave empty to use Auth App above.</p>'])
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
            ->add('oembed_section', 'html', ['html' => '<hr><h5>Instagram / Facebook Reels &amp; Video Embedding (oEmbed)</h5>'
                . '<p class="text-muted small">Dedicated credentials for embedding public Instagram/Facebook Reels and posts '
                . 'inline on product pages and sponsored store videos, via Meta\'s oEmbed API. If left empty, the Marketing App '
                . 'or Auth App credentials above are used instead.</p>'
                . '<div class="alert alert-warning small">'
                . '<strong>This requires a one-time Facebook App Review approval before it will work</strong> — having valid '
                . 'credentials alone is not enough. Steps:'
                . '<ol class="mb-0 mt-2">'
                . '<li>Go to <a href="https://developers.facebook.com/apps" target="_blank">developers.facebook.com/apps</a> and open (or create) an app.</li>'
                . '<li>App Dashboard → Settings → Basic: copy the <strong>App ID</strong> and <strong>App Secret</strong> below. Also set a Privacy Policy URL here (required for review).</li>'
                . '<li>App Dashboard → Settings → Advanced: copy the <strong>Client Token</strong> below (recommended over App Secret for this feature).</li>'
                . '<li>App Dashboard → App Review → Permissions and Features: search for <strong>"oEmbed Read"</strong> and click Request.</li>'
                . '<li>Facebook will ask for a short screen recording showing a Reel/post embedded on this site, plus a written use-case description. Use the "Test oEmbed Connection" box below to prove it works once approved.</li>'
                . '<li>Approval is usually granted within a few days. Until then, the site automatically falls back to a best-effort embed widget.</li>'
                . '</ol></div>'])
            ->add('oembed_app_id', TextField::class,
                TextFieldOption::make()->label('oEmbed App ID')
                    ->value(MarketplaceHelper::getSetting('oembed_app_id', ''))
                    ->placeholder('123456789012345')
            )
            ->add('oembed_app_secret', TextField::class,
                TextFieldOption::make()->label('oEmbed App Secret')
                    ->helperText('Found in App Dashboard → Settings → Basic. Used only if Client Token below is empty.')
                    ->value(MarketplaceHelper::getSetting('oembed_app_secret', ''))
            )
            ->add('oembed_client_token', TextField::class,
                TextFieldOption::make()->label('oEmbed Client Token')
                    ->helperText('Found in App Dashboard → Settings → Advanced. Recommended over App Secret for this feature.')
                    ->value(MarketplaceHelper::getSetting('oembed_client_token', ''))
            )
            ->add('oembed_test_section', 'html', ['html' => view('plugins/marketplace::settings.partials.oembed-test')->render()])
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
