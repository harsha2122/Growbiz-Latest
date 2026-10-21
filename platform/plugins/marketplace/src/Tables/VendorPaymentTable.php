<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\VendorPayment;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class VendorPaymentTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(VendorPayment::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('store_id', function (VendorPayment $item) {
                return $item->store?->name ?? '&mdash;';
            })
            ->editColumn('amount', function (VendorPayment $item) {
                return format_price($item->amount);
            })
            ->editColumn('payment_date', function (VendorPayment $item) {
                return $item->payment_date?->format('d M Y');
            })
            ->editColumn('remarks', function (VendorPayment $item) {
                return $item->remarks ? e($item->remarks) : '&mdash;';
            })
            ->editColumn('operations', function (VendorPayment $item) {
                if (! $item->isPending()) {
                    return '&mdash;';
                }

                $url = route('marketplace.vendor-payments.review', $item->id);
                $token = csrf_token();

                return view('plugins/marketplace::partials.vendor-payment-review-actions', [
                    'url' => $url,
                    'token' => $token,
                    'id' => $item->id,
                ])->render();
            })
            ->rawColumns(['store_id', 'remarks', 'operations']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select(['id', 'store_id', 'transaction_id', 'amount', 'payment_date', 'status', 'remarks', 'created_at'])
            ->with('store:id,name')
            ->latest('id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('store_id')->title(__('Vendor'))->alignStart(),
            Column::make('transaction_id')->title(__('Transaction ID')),
            Column::make('amount')->title(__('Amount')),
            Column::make('payment_date')->title(__('Payment Date')),
            StatusColumn::make(),
            Column::make('remarks')->title(__('Remarks'))->alignStart(),
            Column::make('operations')->title(__('Action'))->orderable(false)->searchable(false),
            CreatedAtColumn::make(),
        ];
    }
}
