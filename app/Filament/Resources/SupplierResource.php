<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Faker\Provider\ar_EG\Text;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupplierResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Filament\Resources\SupplierResource\RelationManagers\PurchaseOrdersRelationManager;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->string()
                    ->required()
                    ->placeholder('Enter the supplier\'s name')
                    ->rules(['required', 'max:255', 'string']),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->placeholder('Enter the supplier\'s email')
                    ->required()
                    ->rules(function ($context, ?Model $record) {
                        return $context === 'create' ? [
                            'required',
                            'email:rfc,dns',
                            'unique:suppliers,email',
                        ] : [
                            'sometimes',
                            'max:20',
                            'email:rfc,dns',
                            Rule::unique('suppliers', 'email')->ignore($record)
                        ];
                    }),
                TextInput::make('phone')
                    ->label('Phone')
                    ->required()
                    ->string()
                    ->placeholder('Enter the supplier\'s phone number')
                    ->rules(function ($context, ?Model $record) {
                        return $context === 'create' ? [
                            'required',
                            'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/',
                            'unique:suppliers,phone',
                        ] : [
                            'sometimes',
                            'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/',
                            Rule::unique('suppliers', 'phone')->ignore($record)
                        ];
                    }),
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

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),
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
            PurchaseOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
