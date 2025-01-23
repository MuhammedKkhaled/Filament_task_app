<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use Filament\Tables;
use App\Models\Product;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LowestStockQuantityProducts extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Products';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->orderBy('stock_quantity')
                    ->limit(5)
            )
            ->striped()
            ->defaultSort('low_stock_quantity', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU'),
                BadgeColumn::make('stock_quantity')
                    ->label('Current Stock')
                    ->colors([
                        'danger' => fn($state): bool => $state === 0,
                        'warning' => fn($state, $record): bool => $state <= $record->low_stock_quantity,
                    ]),
                TextColumn::make('low_stock_quantity')
                    ->label('Low Stock Threshold'),
            ]);
    }
}
