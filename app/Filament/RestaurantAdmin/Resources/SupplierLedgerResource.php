<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\SupplierLedgerResource\Pages;
use App\Models\SupplierLedger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierLedgerResource extends Resource
{
    protected static ?string $model = SupplierLedger::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Procurement';
    protected static ?string $navigationLabel = 'Supplier Ledgers';
    protected static ?string $modelLabel = 'Supplier Ledger';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('created_at')
                    ->label('Posting Date')
                    ->disabled(),
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier')
                    ->disabled(),
                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('INR')
                    ->disabled(),
                Forms\Components\TextInput::make('balance_after')
                    ->label('Running Balance')
                    ->numeric()
                    ->prefix('INR')
                    ->disabled(),
                Forms\Components\TextInput::make('reference_type')
                    ->label('Source Reference Type')
                    ->disabled(),
                Forms\Components\TextInput::make('reference_id')
                    ->label('Source Reference ID')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posting Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'danger' => 'debit',
                        'success' => 'credit',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Running Balance')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Ref Type')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : 'Manual')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference_id')
                    ->label('Ref ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('performer.name')
                    ->label('Recorded By')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'debit' => 'Debit (Payment)',
                        'credit' => 'Credit (Receipt)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSupplierLedgers::route('/'),
        ];
    }
}
