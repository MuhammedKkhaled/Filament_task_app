<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\PurchaseOrderStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ActionGroup;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('total_cost')
                    ->label('Total Cost')
                    ->required()
                    ->numeric()
                    ->columnSpanFull()
                    ->placeholder('Enter the total cost')
                    ->rules([
                        'required',
                        'numeric',
                        'min:0'
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->getStateUsing(function ($record) {
                        if ($record->status === PurchaseOrderStatus::PENDING->value) {
                            return PurchaseOrderStatus::PENDING->value;
                        }
                        return PurchaseOrderStatus::COMPLETED->value;
                    })
                    ->colors([
                        'danger' => PurchaseOrderStatus::PENDING->value,
                        'success' => PurchaseOrderStatus::COMPLETED->value,
                    ]),
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
