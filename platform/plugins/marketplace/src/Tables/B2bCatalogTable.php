<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\B2bCatalog;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;

class B2bCatalogTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(B2bCatalog::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('marketplace.b2b-catalogs.create'))
            ->addActions([
                EditAction::make()->route('marketplace.b2b-catalogs.edit'),
                DeleteAction::make()->route('marketplace.b2b-catalogs.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                Column::make('title')
                    ->title(__('Title'))
                    ->alignStart(),
                Column::make('store_id')
                    ->title(__('Store'))
                    ->alignStart()
                    ->width(180)
                    ->renderUsing(function (Column $column) {
                        $store = $column->getItem()->store;
                        return $store->id ? e($store->name) : '<span class="text-muted">—</span>';
                    }),
                Column::make('description')
                    ->title(__('Description'))
                    ->alignStart()
                    ->width(250),
                Column::make('discount_percentage')
                    ->title(__('Discount'))
                    ->alignCenter()
                    ->width(100)
                    ->renderUsing(function (Column $column) {
                        $value = $column->getItem()->discount_percentage;
                        if ($value > 0) {
                            $formatted = rtrim(rtrim(number_format($value, 2), '0'), '.');

                            return '<span class="badge bg-danger">' . $formatted . '% OFF</span>';
                        }

                        return '<span class="text-muted">-</span>';
                    }),
                Column::make('uploaded_by_type')
                    ->title(__('Uploaded By'))
                    ->alignStart()
                    ->width(120),
                CreatedAtColumn::make(),
            ])
            ->addBulkAction(DeleteBulkAction::make()->permission('marketplace.b2b-catalogs.destroy'))
            ->queryUsing(function ($query) {
                return $query
                    ->with('store:id,name')
                    ->select(['id', 'store_id', 'title', 'description', 'discount_percentage', 'pdf_path', 'uploaded_by', 'uploaded_by_type', 'created_at'])
                    ->latest();
            });
    }
}
