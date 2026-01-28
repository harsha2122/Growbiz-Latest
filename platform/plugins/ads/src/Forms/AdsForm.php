<?php

namespace Botble\Ads\Forms;

use Botble\Ads\Facades\AdsManager;
use Botble\Ads\Http\Requests\AdsRequest;
use Botble\Ads\Models\Ads;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\SortOrderFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdsForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Ads::class)
            ->setValidatorClass(AdsRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('key', TextField::class, [
                'label' => trans('plugins/ads::ads.key'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/ads::ads.key'),
                    'data-counter' => 255,
                ],
                'default_value' => $this->generateAdsKey(),
            ])
            ->add('order', NumberField::class, SortOrderFieldOption::make())
            ->add(
                'ads_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ads::ads.ads_type'))
                    ->choices([
                        'custom_ad' => trans('plugins/ads::ads.custom_ad'),
                        'google_adsense' => 'Google AdSense',
                    ])
            )
            ->addOpenCollapsible('ads_type', 'google_adsense', $this->getModel()->ads_type)
            ->add(
                'google_adsense_slot_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ads::ads.google_adsense_slot_id'))
                    ->placeholder('E.g: 1234567890')
            )
            ->addCloseCollapsible('ads_type', 'google_adsense')
            ->addOpenCollapsible('ads_type', 'custom_ad', $this->getModel()->ads_type ?? 'custom_ad')
            ->add(
                'ad_media_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ads::ads.ad_media_type'))
                    ->choices([
                        'image' => trans('plugins/ads::ads.media_type_image'),
                        'video' => trans('plugins/ads::ads.media_type_video'),
                    ])
                    ->helperText(trans('plugins/ads::ads.ad_media_type_helper'))
            )
            ->add('url', TextField::class, [
                'label' => trans('plugins/ads::ads.url'),
                'attr' => [
                    'placeholder' => trans('plugins/ads::ads.url'),
                    'data-counter' => 255,
                ],
            ])
            ->add('open_in_new_tab', OnOffField::class, [
                'label' => trans('plugins/ads::ads.open_in_new_tab'),
                'default_value' => true,
            ])
            ->addOpenCollapsible('ad_media_type', 'image', $this->getModel()->ad_media_type ?? 'image')
            ->add('image', MediaImageField::class, [
                'label' => trans('plugins/ads::ads.image'),
                'help_block' => [
                    'text' => trans('plugins/ads::ads.image_size_guide'),
                ],
            ])
            ->add('tablet_image', MediaImageField::class, [
                'label' => __('Tablet Image'),
                'help_block' => [
                    'text' => __('For devices with width from 768px to 1200px, if empty, will use the image from the desktop.'),
                ],
            ])
            ->add('mobile_image', MediaImageField::class, [
                'label' => __('Mobile Image'),
                'help_block' => [
                    'text' => __('For devices with width less than 768px, if empty, will use the image from the tablet.'),
                ],
            ])
            ->addCloseCollapsible('ad_media_type', 'image')
            ->addOpenCollapsible('ad_media_type', 'video', $this->getModel()->ad_media_type)
            ->add('video_url', TextField::class, [
                'label' => trans('plugins/ads::ads.video_url'),
                'attr' => [
                    'placeholder' => 'https://www.youtube.com/watch?v=... or https://vimeo.com/...',
                    'data-counter' => 500,
                ],
                'help_block' => [
                    'text' => trans('plugins/ads::ads.video_url_helper'),
                ],
            ])
            ->add('video_thumbnail', MediaImageField::class, [
                'label' => trans('plugins/ads::ads.video_thumbnail'),
                'help_block' => [
                    'text' => trans('plugins/ads::ads.video_thumbnail_helper'),
                ],
            ])
            ->addCloseCollapsible('ad_media_type', 'video')
            ->addCloseCollapsible('ads_type', 'custom_ad')
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->when(($adLocations = AdsManager::getLocations()) && count($adLocations) > 1, function () use ($adLocations): void {
                $this->add(
                    'location',
                    SelectField::class,
                    SelectFieldOption::make()
                        ->label(trans('plugins/ads::ads.location'))
                        ->helperText(trans('plugins/ads::ads.location_helper'))
                        ->choices($adLocations)
                        ->searchable()
                        ->required()
                );
            })
            ->add(
                'expired_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/ads::ads.expired_at'))
                    ->defaultValue(BaseHelper::formatDate(Carbon::now()->addMonth()))
                    ->helperText(trans('plugins/ads::ads.expired_at_helper'))
            )
            ->setBreakFieldPoint('status');
    }

    protected function generateAdsKey(): string
    {
        do {
            $key = strtoupper(Str::random(12));
        } while (Ads::query()->where('key', $key)->exists());

        return $key;
    }
}
