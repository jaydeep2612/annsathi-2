<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\WarehouseResource\Pages;
use App\Filament\RestaurantAdmin\Resources\WarehouseResource\RelationManagers\StocksRelationManager;
use App\Domains\Warehouse\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Inventory & Warehousing';
    protected static ?string $navigationLabel = 'Warehouses';
    protected static ?string $modelLabel = 'Warehouse';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Warehouse Settings')
                    ->description('Details of the central warehouse storage facility')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Central Cold Storage'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('address')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('dispatch_stock')
                    ->label('Dispatch Stock')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('to_branch_id')
                            ->label('Destination Branch')
                            ->relationship('restaurant.branches', 'name')
                            ->required()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('grocery_item_id')
                            ->label('Grocery / Ingredient Item')
                            ->options(function () {
                                return \App\Models\GroceryItem::all()->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->native(false),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->placeholder('0.0000'),
                    ])
                    ->action(function (array $data, Warehouse $record) {
                        try {
                            $service = app(\App\Domains\Warehouse\Services\WarehouseService::class);
                            $service->dispatchStockToBranch(
                                $record->id,
                                (int) $data['to_branch_id'],
                                (int) $data['grocery_item_id'],
                                (float) $data['quantity']
                            );

                            Notification::make()
                                ->title('Stock Dispatched Successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Stock Dispatch Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            StocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
