<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Marketplace\Models\Store;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class CommissionedProductRevenueTable extends TableAbstract
{
    protected $hasOperations = false;

    public function setup(): void
    {
        $this->model(Store::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Store $item) {
                return Html::link(route('marketplace.store.view', $item->id), $item->name);
            })
            ->editColumn('commissioned_qty', function (Store $item) {
                return number_format((float) $item->commissioned_qty);
            })
            ->editColumn('commissioned_sales', function (Store $item) {
                return format_price($item->commissioned_sales);
            })
            ->addColumn('commission_percent', function (Store $item) {
                return number_format($item->commissionPercent(), 2) . '%';
            })
            ->addColumn('commission_earned', function (Store $item) {
                return format_price(((float) $item->commissioned_sales) * $item->commissionPercent() / 100);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'mp_stores.id',
                'mp_stores.name',
                'mp_stores.logo',
            ])
            ->selectRaw('COALESCE(SUM(ec_order_products.qty), 0) as commissioned_qty')
            ->selectRaw('COALESCE(SUM(ec_order_products.qty * ec_order_products.price), 0) as commissioned_sales')
            ->join('ec_products', function ($join): void {
                $join->on('ec_products.store_id', '=', 'mp_stores.id')
                    ->where('ec_products.is_commissioned', true);
            })
            ->join('ec_order_products', 'ec_order_products.product_id', '=', 'ec_products.id')
            ->join('ec_orders', function ($join): void {
                $join->on('ec_orders.id', '=', 'ec_order_products.order_id')
                    ->where('ec_orders.status', OrderStatusEnum::COMPLETED);
            })
            ->groupBy('mp_stores.id', 'mp_stores.name', 'mp_stores.logo');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('name')
                ->title(trans('plugins/marketplace::marketplace.vendor'))
                ->alignStart(),
            Column::make('commissioned_qty')
                ->title(__('Commissioned Units Sold'))
                ->orderable(false)
                ->searchable(false),
            Column::make('commissioned_sales')
                ->title(__('Commissioned Sales'))
                ->orderable(false)
                ->searchable(false),
            Column::make('commission_percent')
                ->title(__('Current Commission %'))
                ->orderable(false)
                ->searchable(false),
            Column::make('commission_earned')
                ->title(__('Commission Earned'))
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function bulkActions(): array
    {
        return [];
    }
}
