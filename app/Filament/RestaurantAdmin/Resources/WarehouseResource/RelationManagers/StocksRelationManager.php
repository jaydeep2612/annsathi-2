<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\WarehouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';
    protected static ?string $title = 'Warehouse Stock Inventory';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('grocery_item_id')
                    ->label('Grocery / Ingredient Item')
                    ->relationship('groceryItem', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->disabled(fn ($context) => $context === 'edit'),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->placeholder('0.0000'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('groceryItem.name')
            ->columns([
                Tables\Columns\TextColumn::make('groceryItem.name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('groceryItem.sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty in Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Stock')
                    ->using(function (array $data) {
                        $warehouseId = (int) $this->getOwnerRecord()->id;
                        $groceryItemId = (int) $data['grocery_item_id'];
                        $quantity = (float) $data['quantity'];

                        $service = app(\App\Domains\Warehouse\Services\WarehouseService::class);
                        $service->addStockToWarehouse($warehouseId, $groceryItemId, $quantity, 0.0);

                        return \App\Domains\Warehouse\Models\WarehouseStock::where('warehouse_id', $warehouseId)
                            ->where('grocery_item_id', $groceryItemId)
                            ->first();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
