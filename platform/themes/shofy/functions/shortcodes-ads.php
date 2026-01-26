<?php

use Botble\Ads\Facades\AdsManager;
use Botble\Ads\Models\Ads;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\UiSelectorFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\UiSelectorField;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;
use Theme\Shofy\Fields\FieldOptions\SliderAutoplayFieldOption;
use Theme\Shofy\Fields\SliderAutoplayField;

app('events')->listen(RouteMatched::class, function (): void {
    if (! is_plugin_active('ads')) {
        return;
    }

    Shortcode::register('ads', __('Ads'), __('Ads'), function (ShortcodeCompiler $shortcode) {
        $ads = [];

        // Location-based display - automatically shows all ads assigned to this location
        if ($shortcode->location && $shortcode->location !== 'not_set') {
            $ads = Ads::query()
                ->where('location', $shortcode->location)
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->where(function ($query) {
                    $query->where('ads_type', 'google_adsense')
                        ->orWhere('expired_at', '>=', Carbon::now());
                })
                ->orderBy('order')
                ->limit($shortcode->limit ? (int) $shortcode->limit : 4)
                ->get()
                ->all();
        }
        // Key-based display - for backward compatibility
        elseif ($shortcode->key_1 || $shortcode->key_2 || $shortcode->key_3 || $shortcode->key_4) {
            $data = Ads::query()
                ->whereIn('key', array_filter([$shortcode->key_1, $shortcode->key_2, $shortcode->key_3, $shortcode->key_4]))
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->where(function ($query) {
                    $query->where('ads_type', 'google_adsense')
                        ->orWhere('expired_at', '>=', Carbon::now());
                })
                ->orderBy('order')
                ->get();

            foreach (range(1, 4) as $i) {
                if ($shortcode->{'key_' . $i}) {
                    $ad = $data->where('key', $shortcode->{'key_' . $i})->first();
                    if ($ad) {
                        $ads[] = $ad;
                    }
                }
            }
        }

        if (empty($ads)) {
            return null;
        }

        return Theme::partial('shortcodes.ads.index', compact('shortcode', 'ads'));
    });

    Shortcode::setPreviewImage('ads', Theme::asset()->url('images/shortcodes/ads/style-1.png'));

    Shortcode::setAdminConfig('ads', function (array $attributes) {
        $ads = AdsManager::getData(true, true)
            ->pluck('name', 'key')
            ->merge(['' => __('-- Select --')])
            ->sortKeys()
            ->all();

        // Get registered ad locations
        $locations = AdsManager::getLocations();

        $styles = [];

        foreach (range(1, 3) as $i) {
            $styles[$i] = [
                'label' => __('Style :number', ['number' => $i]),
                'image' => Theme::asset()->url("images/shortcodes/ads/style-$i.png"),
            ];
        }

        $styles[4] = [
            'label' => __('Slider'),
            'image' => Theme::asset()->url('images/shortcodes/ads/style-4.png'),
        ];

        $styles[5] = [
            'label' => __('Full width'),
        ];

        $form = ShortcodeForm::createFromArray($attributes)
            ->withLazyLoading()
            ->add(
                'style',
                UiSelectorField::class,
                UiSelectorFieldOption::make()
                    ->label(__('Style'))
                    ->choices($styles)
                    ->collapsible('style')
            )
            ->add(
                'location',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Ad Location (Auto-display)'))
                    ->choices($locations)
                    ->helperText(__('Select a location to automatically display all ads assigned to it. This takes priority over individual ad selection below.'))
            )
            ->add(
                'limit',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Max Ads to Display'))
                    ->choices([
                        '' => __('Default (4)'),
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '6' => '6',
                        '8' => '8',
                    ])
                    ->helperText(__('Maximum number of ads to display when using location-based display.'))
            );

        foreach (range(1, 4) as $i) {
            $form->add(
                "key_$i",
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Ad :number (Manual)', ['number' => $i]))
                    ->choices($ads)
                    ->helperText($i === 1 ? __('Or manually select specific ads below (only used if no location is selected above).') : null)
            );
        }

        $form->add(
            'full_width',
            OnOffCheckboxField::class,
            CheckboxFieldOption::make()
                ->label(__('Full width'))
                ->collapseTrigger('style', 2, Arr::get($attributes, 'style', 1) == 2)
        );

        return $form->add(
            'slider_autoplay',
            SliderAutoplayField::class,
            SliderAutoplayFieldOption::make()
        );
    });

    // New shortcode: Display ALL ads in a grid layout
    Shortcode::register('all-ads', __('All Ads'), __('Display all published ads in a grid'), function (ShortcodeCompiler $shortcode) {
        $ads = Ads::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where(function ($query) {
                $query->where('ads_type', 'google_adsense')
                    ->orWhere('expired_at', '>=', Carbon::now());
            })
            ->whereNotNull('image')
            ->orderBy('order')
            ->get();

        if ($ads->isEmpty()) {
            return null;
        }

        $title = $shortcode->title ?: __('All Promotions');
        $columns = $shortcode->columns ?: 3;

        return Theme::partial('shortcodes.ads.all-ads', compact('shortcode', 'ads', 'title', 'columns'));
    });

    Shortcode::setAdminConfig('all-ads', function (array $attributes) {
        return ShortcodeForm::createFromArray($attributes)
            ->withLazyLoading()
            ->add(
                'title',
                'text',
                [
                    'label' => __('Title'),
                    'attr' => [
                        'placeholder' => __('All Promotions'),
                    ],
                ]
            )
            ->add(
                'columns',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Columns'))
                    ->choices([
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ])
                    ->defaultValue('3')
            );
    });
});
