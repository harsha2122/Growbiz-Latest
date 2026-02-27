<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\MetaCampaign;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AdminMetaCampaignTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(MetaCampaign::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('store_name', function (MetaCampaign $item) {
                return $item->store?->name ?? '—';
            })
            ->editColumn('status', function (MetaCampaign $item) {
                $badge = match ($item->status) {
                    'ACTIVE' => 'success',
                    'PAUSED' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-' . $badge . '">' . $item->status . '</span>';
            })
            ->editColumn('spend', function (MetaCampaign $item) {
                return '$' . number_format($item->spend, 2);
            })
            ->editColumn('meta_remote_id', function (MetaCampaign $item) {
                return $item->meta_remote_id
                    ? '<code>' . $item->meta_remote_id . '</code>'
                    : '<span class="text-muted">—</span>';
            })
            ->rawColumns(['status', 'meta_remote_id']);

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        return $this->applyScopes(
            $this->getModel()->query()
                ->with('store:id,name')
                ->select(['id', 'store_id', 'name', 'objective', 'status', 'meta_remote_id', 'impressions', 'clicks', 'spend', 'created_at'])
        );
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('store_name')->title(__('Store'))->alignStart()->orderable(false),
            Column::make('name')->title(__('Campaign Name'))->alignStart(),
            Column::make('objective')->title(__('Objective')),
            Column::make('status')->title(__('Status')),
            Column::make('meta_remote_id')->title(__('Meta ID')),
            Column::make('impressions')->title(__('Impressions')),
            Column::make('clicks')->title(__('Clicks')),
            Column::make('spend')->title(__('Spend')),
            CreatedAtColumn::make(),
        ];
    }
}
