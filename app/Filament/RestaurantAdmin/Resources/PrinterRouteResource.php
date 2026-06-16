<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\PrinterRouteResource\Pages;
use App\Models\PrinterRoute;
use App\Models\KitchenStation;
use App\Models\Category;
use App\Models\PrinterGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrinterRouteResource extends Resource
{
    protected static ?string $model = PrinterRoute::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Printer Routes';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $modelLabel = 'Printer Route';
    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Route Configuration')
                    ->schema([
                        Forms\Components\Select::make('kitchen_station_id')
                            ->label('Kitchen Station')
                            ->options(KitchenStation::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->hint('Route items from this station'),
                        Forms\Components\Select::make('category_id')
                            ->label('Menu Category')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->hint('Or route items from this category'),
                        Forms\Components\Select::make('printer_group_id')
                            ->label('Printer Group')
                            ->options(PrinterGroup::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('route_type')
                            ->options([
                                'kot' => 'KOT (Kitchen Order Ticket)',
                                'receipt' => 'Receipt',
                                'invoice' => 'Invoice',
                            ])
                            ->default('kot')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kitchenStation.name')
                    ->label('Kitchen Station')
                    ->default('All Stations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Menu Category')
                    ->default('All Categories')
                    ->sortable(),
                Tables\Columns\TextColumn::make('printerGroup.name')
                    ->label('Printer Group')
                    ->sortable(),
                Tables\Columns\TextColumn::make('route_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kot' => 'warning',
                        'receipt' => 'success',
                        'invoice' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('route_type')
                    ->options([
                        'kot' => 'KOT',
                        'receipt' => 'Receipt',
                        'invoice' => 'Invoice',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrinterRoutes::route('/'),
            'create' => Pages\CreatePrinterRoute::route('/create'),
            'edit' => Pages\EditPrinterRoute::route('/{record}/edit'),
        ];
    }
}
