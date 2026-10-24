<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\Enquiry;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class EnquiryTable extends TableAbstract
{
    protected $hasOperations = false;

    protected int $storeId = 0;

    public function setup(): void
    {
        $this->model(Enquiry::class);
    }

    public function forStore(int $storeId): static
    {
        $this->storeId = $storeId;

        return $this;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product_id', function (Enquiry $item) {
                return $item->product?->name ?? '&mdash;';
            })
            ->editColumn('source', function (Enquiry $item) {
                return match ($item->source) {
                    'product_page' => __('Product Page'),
                    'whatsapp' => __('WhatsApp'),
                    default => __('Vendor Page'),
                };
            })
            ->editColumn('email', function (Enquiry $item) {
                return $item->email ?: '&mdash;';
            })
            ->editColumn('address', function (Enquiry $item) {
                return $item->address ?: '&mdash;';
            })
            ->rawColumns(['product_id', 'email', 'address']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select(['id', 'store_id', 'product_id', 'source', 'name', 'contact_number', 'email', 'address', 'created_at'])
            ->with('product:id,name')
            ->where('store_id', $this->storeId);

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
            Column::make('product_id')->title(__('Product')),
            Column::make('source')->title(__('Source')),
            CreatedAtColumn::make(),
        ];
    }
}
