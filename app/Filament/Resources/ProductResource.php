<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\ProductResource\Pages;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                    ])
                    ->placeholder('Select the category')
                    ->rules(['required', 'exists:categories,id']),
            ]);
    }

    public static function table(Table $table): Table
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
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload()
            ])
            ->actions([

                ActionGroup::make([
                    DeleteAction::make(),
                    Tables\Actions\EditAction::make(),
                ])

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
