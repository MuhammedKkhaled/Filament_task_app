<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderItemResource\Pages;
use App\Filament\Resources\PurchaseOrderItemResource\RelationManagers;
use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                    ->options(
                        PurchaseOrderResource::all()
                            ->map(fn($record) => "{$record->id} - Total-Cost", 'id')
                            ->toArray(),
                    )
                    ->displayUsingLabels()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
