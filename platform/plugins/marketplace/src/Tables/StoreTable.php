<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\Html;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Models\Store;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class StoreTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Store::class)
            ->addActions([
                ViewAction::make()
                    ->route('marketplace.store.view')
                    ->permission('marketplace.store.view'),
                EditAction::make()->route('marketplace.store.edit'),
                DeleteAction::make()->route('marketplace.store.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                $name = Html::link(route('marketplace.store.edit', $item->id), $item->name);
                if ($item->is_verified) {
                    $name .= ' ' . view('plugins/marketplace::partials.verified-badge', ['size' => 'sm'])->render();
                }

                return $name;
            })
            ->editColumn('earnings', function ($item) {
                return $item->customer->id ? format_price($item->customer->balance ?: 0) : '--';
            })
            ->editColumn('products_count', function ($item) {
                return $item->products_count;
            })
            ->addColumn('customer_name', function ($item) {
                if (! $item->customer->name) {
                    return '&mdash;';
                }

                return Html::link(route('customers.edit', $item->customer->id), $item->customer->name);
            })
            ->editColumn('is_key_account', function ($item) {
                $checked = $item->is_key_account ? 'checked' : '';
                $url = route('marketplace.store.toggle-key-account', $item->id);

                return '<div class="form-check form-switch"><input type="checkbox" class="form-check-input toggle-key-account" data-url="' . $url . '" ' . $checked . '></div>';
            })
            ->editColumn('is_commissioned_enabled', function ($item) {
                $checked = $item->commissionSetting?->is_enabled ? 'checked' : '';
                $url = route('marketplace.store.update-commission-setting', $item->id);

                return '<div class="form-check form-switch"><input type="checkbox" class="form-check-input toggle-commission-enabled" data-url="' . $url . '" ' . $checked . '></div>';
            })
            ->editColumn('commission_max_products', function ($item) {
                $url = route('marketplace.store.update-commission-setting', $item->id);

                return '<input type="number" min="0" class="form-control form-control-sm commission-max-products" data-url="' . $url . '" value="' . e($item->commissionSetting?->max_products) . '" placeholder="∞" style="width:90px">';
            })
            ->editColumn('commission_percent', function ($item) {
                $url = route('marketplace.store.update-commission-setting', $item->id);

                return '<input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm commission-percent" data-url="' . $url . '" value="' . e($item->commissionSetting?->commission_percent) . '" style="width:90px">';
            })
            ->editColumn('is_minimal_profile', function ($item) {
                $checked = $item->is_minimal_profile ? 'checked' : '';
                $url = route('marketplace.store.toggle-minimal-profile', $item->id);

                return '<div class="form-check form-switch"><input type="checkbox" class="form-check-input toggle-minimal-profile" data-url="' . $url . '" ' . $checked . '></div>';
            })
            ->editColumn('gallery_image_limit', function ($item) {
                $url = route('marketplace.store.update-gallery-limit', $item->id);

                return '<input type="number" min="0" class="form-control form-control-sm gallery-image-limit" data-url="' . $url . '" value="' . e($item->gallery_image_limit) . '" style="width:90px">';
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
                'logo',
                'name',
                'created_at',
                'status',
                'customer_id',
                'is_verified',
                'is_key_account',
                'is_minimal_profile',
                'gallery_image_limit',
            ])
            ->with(['customer', 'customer.vendorInfo', 'commissionSetting'])
            ->withCount(['products']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make('logo')
                ->title(trans('plugins/marketplace::store.forms.logo')),
            NameColumn::make()->route('marketplace.store.edit'),
            Column::make('earnings')
                ->title(trans('plugins/marketplace::marketplace.tables.earnings'))
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->width('100'),
            Column::make('products_count')
                ->title(trans('plugins/marketplace::marketplace.tables.products_count'))
                ->orderable(false)
                ->searchable(false),
            Column::make('customer_name')
                ->title(trans('plugins/marketplace::marketplace.vendor'))
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->printable(false),
            Column::make('is_key_account')
                ->title(trans('Key Account'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            Column::make('is_commissioned_enabled')
                ->title(trans('Commission Access'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            Column::make('commission_max_products')
                ->title(trans('Commission Product Limit'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            Column::make('commission_percent')
                ->title(trans('Commission %'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            Column::make('is_minimal_profile')
                ->title(trans('Minimal Profile'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            Column::make('gallery_image_limit')
                ->title(trans('Gallery Limit'))
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.store.create'), 'marketplace.store.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.store.destroy'),
        ];
    }

    public function htmlDrawCallbackFunction(): ?string
    {
        return parent::htmlDrawCallbackFunction() . "
            $('.toggle-key-account').off('change').on('change', function () {
                var toggle = $(this);
                $.ajax({
                    url: toggle.data('url'),
                    type: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')},
                    success: function (response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function () {
                        toggle.prop('checked', !toggle.prop('checked'));
                        Botble.showError('Failed to toggle key account status.');
                    }
                });
            });

            $('.toggle-commission-enabled').off('change').on('change', function () {
                var toggle = $(this);
                $.ajax({
                    url: toggle.data('url'),
                    type: 'POST',
                    data: {is_enabled: toggle.prop('checked') ? 1 : 0},
                    headers: {'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')},
                    success: function (response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function () {
                        toggle.prop('checked', !toggle.prop('checked'));
                        Botble.showError('Failed to update commission access.');
                    }
                });
            });

            $('.commission-max-products, .commission-percent').off('change').on('change', function () {
                var input = $(this);
                var field = input.hasClass('commission-max-products') ? 'max_products' : 'commission_percent';
                var data = {};
                data[field] = input.val();
                $.ajax({
                    url: input.data('url'),
                    type: 'POST',
                    data: data,
                    headers: {'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')},
                    success: function (response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function () {
                        Botble.showError('Failed to update commission setting.');
                    }
                });
            });

            $('.toggle-minimal-profile').off('change').on('change', function () {
                var toggle = $(this);
                $.ajax({
                    url: toggle.data('url'),
                    type: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')},
                    success: function (response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function () {
                        toggle.prop('checked', !toggle.prop('checked'));
                        Botble.showError('Failed to toggle minimal profile mode.');
                    }
                });
            });

            $('.gallery-image-limit').off('change').on('change', function () {
                var input = $(this);
                $.ajax({
                    url: input.data('url'),
                    type: 'POST',
                    data: {gallery_image_limit: input.val()},
                    headers: {'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')},
                    success: function (response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function () {
                        Botble.showError('Failed to update gallery limit.');
                    }
                });
            });
        ";
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => StoreStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', StoreStatusEnum::values()),
            ],
            'is_verified' => [
                'title' => trans('plugins/marketplace::store.forms.is_verified'),
                'type' => 'select',
                'choices' => [
                    0 => trans('core/base::base.no'),
                    1 => trans('core/base::base.yes'),
                ],
                'validate' => 'required|in:0,1',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
