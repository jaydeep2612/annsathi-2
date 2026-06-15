<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\TaxRateResource\Pages;
use App\Domains\Tax\Models\TaxRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Menu & Taxes';
    protected static ?string $navigationLabel = 'Tax Rates';
    protected static ?string $modelLabel = 'Tax Rate';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tax Rate Configuration')
                    ->description('Set name and specific percentage or fixed values')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., CGST 2.5% or Service Charge 10%'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => 'Percentage (%)',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->required()
                            ->default('percentage')
                            ->native(false),
                        Forms\Components\TextInput::make('rate')
                            ->required()
                            ->numeric()
                            ->placeholder('e.g., 2.50 or 10.00'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->state(fn (TaxRate $record) => $record->type === 'percentage' ? "{$record->rate}%" : $record->rate)
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'percentage',
                        'info' => 'fixed',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxRates::route('/'),
            'create' => Pages\CreateTaxRate::route('/create'),
            'edit' => Pages\EditTaxRate::route('/{record}/edit'),
        ];
    }
}
