<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Http\Requests\SubscriptionPlanRequest;
use Botble\Marketplace\Models\SubscriptionPlan;

class SubscriptionPlanForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(SubscriptionPlan::class)
            ->setValidatorClass(SubscriptionPlanRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->toArray())
            ->add(
                'max_products',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/marketplace::subscription-plan.max_products'))
                    ->helperText(trans('plugins/marketplace::subscription-plan.max_products_help'))
                    ->defaultValue(10)
                    ->toArray()
            )
            ->add(
                'duration_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/marketplace::subscription-plan.duration_days'))
                    ->helperText(trans('plugins/marketplace::subscription-plan.duration_days_help'))
                    ->defaultValue(30)
                    ->toArray()
            )
            ->add(
                'price',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/marketplace::subscription-plan.price'))
                    ->helperText(trans('plugins/marketplace::subscription-plan.price_help'))
                    ->defaultValue(0)
                    ->toArray()
            )
            ->add(
                'sort_order',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/marketplace::subscription-plan.sort_order'))
                    ->defaultValue(0)
                    ->toArray()
            )
            ->add(
                'is_default',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/marketplace::subscription-plan.is_default'))
                    ->helperText(trans('plugins/marketplace::subscription-plan.is_default_help'))
                    ->defaultValue(false)
                    ->toArray()
            )
            ->add(
                'is_active',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/marketplace::subscription-plan.is_active'))
                    ->defaultValue(true)
                    ->toArray()
            );
    }
}
