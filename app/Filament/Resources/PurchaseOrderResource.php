<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PurchaseOrder;
use Filament\Resources\Resource;
use App\Enums\PurchaseOrderStatus;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers\ItemsRelationManager;

use function PHPUnit\Framework\returnSelf;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->rules(['required'])
                    ->createOptionForm([

                        Forms\Components\TextInput::make('name')
                            ->rules(['required', 'max:255'])
                            ->required(),

                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->unique('suppliers', 'email')
                            ->placeholder('Enter the supplier email')
                            ->rules(['required', 'email', 'unique:suppliers,email']),

                        TextInput::make('phone')
                            ->required()
                            ->unique('suppliers', 'phone')
                            ->placeholder('Enter the supplier phone')
                            ->rules([
                                'required',
                                'unique:suppliers,phone',
                                'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/',
                            ]),
                    ]),

                TextInput::make('total_cost')
                    ->label('Total Cost')
                    ->required()
                    ->numeric()
                    ->placeholder('Enter the total cost')
                    ->rules([
                        'required',
                        'numeric',
                        'min:0'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
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
            ->actions([
                ActionGroup::make([
                    Action::make('receiveStock')
                        ->label('Receive Stock')
                        ->icon('heroicon-o-truck')
                        ->requiresConfirmation()
                        ->action(function (PurchaseOrder $record) {

                            // Iterate through the purchase order items
                            foreach ($record->items as $item) {
                                // Find the product
                                $product = $item->product;

                                // Update the product's stock quantity
                                $product->stock_quantity += $item->quantity;
                                $product->save();
                            }

                            // Mark the purchase order as completed
                            $record->status = PurchaseOrderStatus::COMPLETED->value;
                            $record->save();
                        })
                        ->visible(function (PurchaseOrder $record) {
                            return $record->status === PurchaseOrderStatus::PENDING->value;
                        }),

                    DeleteAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
