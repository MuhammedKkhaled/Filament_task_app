<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->preload()
                    ->rules(['required', 'exists:products,id'])
                    ->createOptionForm([

                        Forms\Components\TextInput::make('name')
                            ->rules(['required', 'max:255'])
                            ->required(),

                        Forms\Components\TextInput::make('sku')
                            ->rules(['required', 'max:255'])
                            ->required(),

                        Forms\Components\TextInput::make('stock_quantity')
                            ->rules(['required', 'integer', 'min:1'])
                            ->integer()
                            ->label('Stock Quantity')
                            ->placeholder('Enter the stock quantity')
                            ->required(),

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
                            ->placeholder('Select the category')
                            ->rules(['required', 'exists:categories,id']),
                    ]),

                TextInput::make('quantity')
                    ->required()
                    ->integer()
                    ->label('Quantity')
                    ->placeholder('Enter the quantity')
                    ->minValue(1)
                    ->rules([
                        'required',
                        'integer',
                        'min:1',
                        function ($get) {
                            return function ($attribute, $value, $fail) use ($get) {
                                $productId = $get('product_id');
                                if ($productId) {
                                    $product = Product::find($productId);
                                    if ($value > $product->stock_quantity) {
                                        $fail("The quantity cannot exceed available stock ({$product->stock_quantity}).");
                                    }
                                }
                            };
                        },
                    ]),
                TextInput::make('price')
                    ->numeric()
                    ->reactive()
                    ->required()
                    ->label('Price')
                    ->placeholder('Enter the price')
                    ->rules(['required', 'numeric', 'min:0']),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                TextColumn::make('product.name')->label('Product'),
                TextColumn::make('quantity')->label('Quantity')->searchable(),
                TextColumn::make('price')->label('Price')->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->before(function ($data) {
                    $product = Product::find($data['product_id']);
                    $product->stock_quantity -= intval($data['quantity']);
                    $product->save();
                }),
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
