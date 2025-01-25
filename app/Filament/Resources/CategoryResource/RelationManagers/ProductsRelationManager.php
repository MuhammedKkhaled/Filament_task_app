<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ActionGroup;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->placeholder('Enter the product name')
                    ->rules(['required', 'string', 'max:255']),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->placeholder('Enter the product SKU')
                    ->rules(function ($context, ?Model $record) {
                        return $context === 'create' ? [
                            'required',
                            'string',
                            'max:255',
                            'unique:products,sku',
                        ] : [
                            'required',
                            'string',
                            'max:255',
                            Rule::unique('products', 'sku')->ignore($record)
                        ];
                    }),
                TextInput::make('stock_quantity')
                    ->required()
                    ->integer()
                    ->label('Stock Quantity')
                    ->placeholder('Enter the stock quantity')
                    ->minValue(1)
                    ->rules(['required', 'integer', 'min:1']),
                TextInput::make('low_stock_quantity')
                    ->required()
                    ->integer()
                    ->label('Low Stock Quantity')
                    ->placeholder('Enter the low stock quantity')
                    ->minValue(1)
                    ->rules(['required', 'integer', 'min:1']),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label('Stock Quantity')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('low_stock_quantity')
                    ->label('Low Stock Quantity')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('stock_status')
                    ->label('Stock Status')
                    ->getStateUsing(function ($record) {
                        if ($record->stock_quantity === 0) {
                            return 'Out of Stock';
                        }
                        if ($record->stock_quantity <= $record->low_stock_quantity) {
                            return 'Low Stock';
                        }
                        return 'In Stock';
                    })
                    ->colors([
                        'danger' => 'Out of Stock',
                        'warning' => 'Low Stock',
                        'success' => 'In Stock',
                    ]),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
