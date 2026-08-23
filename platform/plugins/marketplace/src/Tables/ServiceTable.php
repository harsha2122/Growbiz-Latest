<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\Service;
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

class ServiceTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Service::class)
            ->addActions([
                EditAction::make()->route('marketplace.services.edit'),
                DeleteAction::make()->route('marketplace.services.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('store_id', function (Service $item) {
                return $item->store?->name ?: '&mdash;';
            })
            ->editColumn('price', function (Service $item) {
                return $item->price ? format_price($item->price) : __('Contact for price');
            })
            ->editColumn('clicks_today', fn (Service $item) => number_format($item->clicksCount('today')))
            ->editColumn('clicks_30days', fn (Service $item) => number_format($item->clicksCount('30days')))
            ->editColumn('clicks_total', fn (Service $item) => number_format($item->clicksCount()));

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with('store')
            ->select([
                'id',
                'store_id',
                'name',
                'price',
                'status',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('marketplace.services.edit'),
            Column::make('store_id')
                ->title(__('Vendor / Store'))
                ->width(180),
            Column::make('price')
                ->title(__('Price'))
                ->width(120),
            Column::make('clicks_today')
                ->title(__('Book Now Clicks (Today)'))
                ->width(120)
                ->orderable(false)
                ->searchable(false),
            Column::make('clicks_30days')
                ->title(__('Book Now Clicks (30d)'))
                ->width(120)
                ->orderable(false)
                ->searchable(false),
            Column::make('clicks_total')
                ->title(__('Book Now Clicks (Total)'))
                ->width(120)
                ->orderable(false)
                ->searchable(false),
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.services.create'), 'marketplace.services.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.services.destroy'),
        ];
    }
}
