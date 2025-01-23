<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderItemResource\Pages;
use App\Filament\Resources\PurchaseOrderItemResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderItemResource extends Resource
{
    protected static ?string $model = PurchaseOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->minValue(1)
                    ->rules(['required', 'min:1', 'max:200'])
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->minValue(0.01)
                    ->rules(['required', 'min:0.01', 'max:999999.99'])
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
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
            'index' => Pages\ListPurchaseOrderItems::route('/'),
            'create' => Pages\CreatePurchaseOrderItem::route('/create'),
            'edit' => Pages\EditPurchaseOrderItem::route('/{record}/edit'),
        ];
    }
}
