<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Tables\Traits\ForVendor;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class MetaAdTable extends TableAbstract
{
    use ForVendor;

    protected int $adSetId = 0;

    public function setup(): void
    {
        $this->model(MetaAd::class);
    }

    public function forAdSet(int $adSetId): static
    {
        $this->adSetId = $adSetId;

        return $this;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('status', function (MetaAd $item) {
                $badge = match ($item->status) {
                    'ACTIVE' => 'success',
                    'PAUSED' => 'warning',
                    'IN_REVIEW' => 'info',
                    default => 'secondary',
                };

                return '<span class="badge bg-' . $badge . '">' . $item->status . '</span>';
            })
            ->editColumn('spend', function (MetaAd $item) {
                return '$' . number_format($item->spend, 2);
            })
            ->editColumn('ctr', function (MetaAd $item) {
                return number_format($item->ctr, 2) . '%';
            })
            ->editColumn('operations', function (MetaAd $item) {
                return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ads.table-actions', compact('item'))->render();
            })
            ->rawColumns(['status', 'operations']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $storeId = auth('customer')->user()?->store?->id;

        $query = $this->getModel()->query()
            ->select(['id', 'name', 'format', 'status', 'impressions', 'clicks', 'ctr', 'spend', 'cpc', 'created_at'])
            ->where('store_id', $storeId);

        if ($this->adSetId) {
            $query->where('ad_set_id', $this->adSetId);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('name')->title(__('Ad Name'))->alignStart(),
            Column::make('format')->title(__('Format')),
            Column::make('status')->title(__('Status')),
            Column::make('impressions')->title(__('Impressions')),
            Column::make('clicks')->title(__('Clicks')),
            Column::make('ctr')->title(__('CTR')),
            Column::make('spend')->title(__('Spend')),
            Column::make('cpc')->title(__('CPC')),
            CreatedAtColumn::make(),
        ];
    }
}
