<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Tables\Traits\ForVendor;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class MetaAdSetTable extends TableAbstract
{
    use ForVendor;

    protected int $campaignId = 0;

    public function setup(): void
    {
        $this->model(MetaAdSet::class);
    }

    public function forCampaign(int $campaignId): static
    {
        $this->campaignId = $campaignId;

        return $this;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('status', function (MetaAdSet $item) {
                $badge = match ($item->status) {
                    'ACTIVE' => 'success',
                    'PAUSED' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-' . $badge . '">' . $item->status . '</span>';
            })
            ->editColumn('spend', function (MetaAdSet $item) {
                return '$' . number_format($item->spend, 2);
            })
            ->editColumn('operations', function (MetaAdSet $item) {
                return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ad-sets.table-actions', compact('item'))->render();
            })
            ->rawColumns(['status', 'operations']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $storeId = auth('customer')->user()?->store?->id;

        $query = $this->getModel()->query()
            ->select(['id', 'name', 'campaign_id', 'status', 'daily_budget', 'impressions', 'clicks', 'spend', 'created_at'])
            ->where('store_id', $storeId);

        if ($this->campaignId) {
            $query->where('campaign_id', $this->campaignId);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('name')->title(__('Ad Set Name'))->alignStart(),
            Column::make('status')->title(__('Status')),
            Column::make('daily_budget')->title(__('Daily Budget')),
            Column::make('impressions')->title(__('Impressions')),
            Column::make('clicks')->title(__('Clicks')),
            Column::make('spend')->title(__('Spend')),
            CreatedAtColumn::make(),
        ];
    }
}
