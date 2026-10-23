<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\Html;
use Botble\Marketplace\Models\Enquiry;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AllEnquiriesTable extends TableAbstract
{
    protected $hasOperations = false;

    public function setup(): void
    {
        $this->model(Enquiry::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('store_id', function (Enquiry $item) {
                return $item->store?->name
                    ? Html::link(route('marketplace.enquiries.vendor', $item->store_id), $item->store->name)
                    : '&mdash;';
            })
            ->editColumn('product_id', function (Enquiry $item) {
                return $item->product?->name ?? '&mdash;';
            })
            ->editColumn('email', function (Enquiry $item) {
                return $item->email ?: '&mdash;';
            })
            ->editColumn('address', function (Enquiry $item) {
                return $item->address ?: '&mdash;';
            })
            ->editColumn('source', function (Enquiry $item) {
                return match ($item->source) {
                    'product_page' => __('Product Page'),
                    'whatsapp' => __('WhatsApp'),
                    default => __('Vendor Page'),
                };
            })
            ->filterColumn('store_id', function (Builder $query, $keyword): void {
                $query->whereHas('store', fn (Builder $query) => $query->where('name', 'like', '%' . $keyword . '%'));
            })
            ->filterColumn('product_id', function (Builder $query, $keyword): void {
                $query->whereHas('product', fn (Builder $query) => $query->where('name', 'like', '%' . $keyword . '%'));
            })
            ->rawColumns(['store_id', 'product_id', 'email', 'address']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select(['id', 'store_id', 'product_id', 'source', 'name', 'contact_number', 'email', 'address', 'created_at'])
            ->with(['store:id,name', 'product:id,name'])
            ->latest('id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('name')->title(__('Name'))->alignStart(),
            Column::make('contact_number')->title(__('Contact')),
            Column::make('email')->title(__('Email')),
            Column::make('address')->title(__('Address')),
            Column::make('store_id')->title(trans('plugins/marketplace::store.store'))->alignStart(),
            Column::make('product_id')->title(__('Product'))->alignStart(),
            Column::make('source')->title(__('Source')),
            CreatedAtColumn::make(),
        ];
    }

    public function bulkActions(): array
    {
        return [];
    }
}
