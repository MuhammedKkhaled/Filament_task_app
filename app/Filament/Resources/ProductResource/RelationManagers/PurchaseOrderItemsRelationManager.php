<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PurchaseOrder;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;

class PurchaseOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('purchase_order_id')
                    ->label('Purchase Order')
                    ->options(
                        PurchaseOrder::query()->get()
                            ->mapWithKeys(fn($record) => [$record->id => "{$record->id} - {$record->total_cost} - Supplier ( {$record->supplier->name}  )"])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->minValue(1)
                    ->rules([
                        'required',
                        'min:1',
                        'max:200',
                        'required',
                        'min:1',
                        'max:200',
                        function ($get) {
                            return function ($attribute, $value, $fail) use ($get) {
                                $productId = $this->ownerRecord->id;
                                if ($productId) {
                                    $product = Product::find($productId);
                                    if ($value > $product->stock_quantity) {
                                        $fail("The quantity cannot exceed available stock ({$product->stock_quantity}).");
                                    }
                                }
                            };
                        }
                    ])
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->minValue(0.01)
                    ->columnSpanFull()
                    ->rules(['required', 'min:0.01', 'max:999999.99'])
                    ->required(),



            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchaseOrder.total_cost')
                    ->label('Purchase Order'),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Quantity'),
                TextColumn::make('price')
                    ->label('Price'),
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
