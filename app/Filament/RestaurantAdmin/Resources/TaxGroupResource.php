<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\TaxGroupResource\Pages;
use App\Domains\Tax\Models\TaxGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxGroupResource extends Resource
{
    protected static ?string $model = TaxGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationGroup = 'Menu & Taxes';
    protected static ?string $navigationLabel = 'Tax Groups';
    protected static ?string $modelLabel = 'Tax Group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tax Group Details')
                    ->description('Create a tax group and link active rates')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., GST 5% or VAT 15%'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                        Forms\Components\Select::make('rates')
                            ->multiple()
                            ->relationship('rates', 'name')
                            ->preload()
                            ->native(false)
                            ->columnSpanFull()
                            ->helperText('Select the components of this tax group (e.g. CGST + SGST)'),
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
                Tables\Columns\TextColumn::make('rates.name')
                    ->badge()
                    ->label('Rates In Group'),
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
            'index' => Pages\ListTaxGroups::route('/'),
            'create' => Pages\CreateTaxGroup::route('/create'),
            'edit' => Pages\EditTaxGroup::route('/{record}/edit'),
        ];
    }
}
