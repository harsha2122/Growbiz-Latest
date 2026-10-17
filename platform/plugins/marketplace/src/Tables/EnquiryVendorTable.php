<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\Html;
use Botble\Marketplace\Models\Store;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class EnquiryVendorTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(Store::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Store $item) {
                return Html::link(route('marketplace.enquiries.vendor', $item->id), $item->name);
            })
            ->editColumn('enquiries_count', function (Store $item) {
                return '<span class="badge bg-info">' . $item->enquiries_count . '</span>';
            })
            ->editColumn('details_visible', function (Store $item) {
                $checked = $item->enquiryVisibility?->details_visible ? 'checked' : '';
                $url = route('marketplace.enquiries.toggle-visibility');
                $token = csrf_token();

                return '<input type="checkbox" ' . $checked . ' onchange="'
                    . "fetch('" . $url . "',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'" . $token . "'},body:JSON.stringify({store_id:" . $item->id . ",visible:this.checked?1:0})})"
                    . '">';
            })
            ->editColumn('operations', function (Store $item) {
                return '<a href="' . route('marketplace.enquiries.vendor', $item->id) . '" class="btn btn-sm btn-primary">' . __('Request Enquiries') . '</a>';
            })
            ->rawColumns(['enquiries_count', 'details_visible', 'operations']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select(['id', 'name'])
            ->withCount('enquiries')
            ->with('enquiryVisibility')
            ->having('enquiries_count', '>', 0);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('name')->title(__('Vendor'))->alignStart(),
            Column::make('enquiries_count')->title(__('Enquiries')),
            Column::make('details_visible')->title(__('Reveal Details to Vendor')),
            Column::make('operations')->title(__('Action')),
        ];
    }
}
