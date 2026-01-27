<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\SubscriptionPlan;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class SubscriptionPlanTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SubscriptionPlan::class)
            ->addActions([
                EditAction::make()->route('marketplace.subscription-plans.edit'),
                DeleteAction::make()->route('marketplace.subscription-plans.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('max_products', function ($item) {
                return $item->max_products == 0 ? __('Unlimited') : $item->max_products;
            })
            ->editColumn('duration_days', function ($item) {
                return $item->duration_text;
            })
            ->editColumn('price', function ($item) {
                return $item->price > 0 ? format_price($item->price) : __('Free');
            })
            ->editColumn('is_default', function ($item) {
                return $item->is_default
                    ? '<span class="badge bg-success">' . __('Yes') . '</span>'
                    : '<span class="badge bg-secondary">' . __('No') . '</span>';
            })
            ->editColumn('is_active', function ($item) {
                return $item->is_active
                    ? '<span class="badge bg-success">' . __('Active') . '</span>'
                    : '<span class="badge bg-danger">' . __('Inactive') . '</span>';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'max_products',
                'duration_days',
                'price',
                'is_default',
                'is_active',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('marketplace.subscription-plans.edit'),
            Column::make('max_products')
                ->title(trans('plugins/marketplace::subscription-plan.max_products'))
                ->width(120),
            Column::make('duration_days')
                ->title(trans('plugins/marketplace::subscription-plan.duration'))
                ->width(120),
            Column::make('price')
                ->title(trans('plugins/marketplace::subscription-plan.price'))
                ->width(100),
            Column::make('is_default')
                ->title(trans('plugins/marketplace::subscription-plan.is_default'))
                ->width(100),
            Column::make('is_active')
                ->title(trans('plugins/marketplace::subscription-plan.status'))
                ->width(100),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.subscription-plans.create'), 'marketplace.subscription-plans.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.subscription-plans.destroy'),
        ];
    }
}
