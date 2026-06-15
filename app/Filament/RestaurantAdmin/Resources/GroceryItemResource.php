<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\GroceryItemResource\Pages;
use App\Models\GroceryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroceryItemResource extends Resource
{
    protected static ?string $model = GroceryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Inventory & Warehousing';
    protected static ?string $navigationLabel = 'Raw Stock Items';
    protected static ?string $modelLabel = 'Raw Stock Item';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->description('Specify name, SKU, unit of measurement, and vendor bindings')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU Code')
                            ->maxLength(50),
                        Forms\Components\Select::make('measurement_unit_id')
                            ->relationship('measurementUnit', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->placeholder('No supplier bound')
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->placeholder('Global / Central Stock template')
                            ->preload()
                            ->searchable()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Stock Levels & Reorder Thresholds')
                    ->description('Set alerts and base values for stock replenishment')
                    ->schema([
                        Forms\Components\TextInput::make('current_stock')
                            ->numeric()
                            ->default(0.0000)
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit')
                            ->helperText('Initial quantity. Can only be adjusted via movements/transactions after creation.'),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->numeric()
                            ->default(0.0000)
                            ->required()
                            ->helperText('Alert threshold for replenishment'),
                        Forms\Components\TextInput::make('reorder_quantity')
                            ->numeric()
                            ->placeholder('e.g., 50.0000'),
                        Forms\Components\TextInput::make('cost_per_unit')
                            ->numeric()
                            ->placeholder('0.00')
                            ->prefix('INR'),
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
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->placeholder('Global Catalog'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stock Level')
                    ->sortable()
                    ->badge()
                    ->color(fn (GroceryItem $record) => $record->current_stock <= $record->low_stock_threshold ? 'danger' : 'success')
                    ->state(fn (GroceryItem $record) => $record->current_stock . ' ' . $record->measurementUnit->short_name),
                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label('Min Alert Level')
                    ->state(fn (GroceryItem $record) => $record->low_stock_threshold . ' ' . $record->measurementUnit->short_name),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Cost/Unit')
                    ->money('INR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock Alert')
                    ->query(fn ($query) => $query->whereColumn('current_stock', '<=', 'low_stock_threshold')),
            ])
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
            'index' => Pages\ListGroceryItems::route('/'),
            'create' => Pages\CreateGroceryItem::route('/create'),
            'edit' => Pages\EditGroceryItem::route('/{record}/edit'),
        ];
    }
}
