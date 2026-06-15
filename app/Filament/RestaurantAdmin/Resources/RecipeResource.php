<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\RecipeResource\Pages;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Inventory & Warehousing';
    protected static ?string $navigationLabel = 'Recipe Ingredients';
    protected static ?string $modelLabel = 'Recipe Ingredient';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Scope')
                    ->description('Choose target menu item and option variant')
                    ->schema([
                        Forms\Components\Select::make('menu_item_id')
                            ->relationship('menuItem', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('item_variant_id')
                            ->label('Option Variant (Optional)')
                            ->relationship('itemVariant', 'label')
                            ->placeholder('All variants / Base item')
                            ->preload()
                            ->searchable()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Ingredient Details')
                    ->description('Select required raw material ingredient and quantities')
                    ->schema([
                        Forms\Components\Select::make('grocery_item_id')
                            ->label('Raw Ingredient')
                            ->relationship('groceryItem', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('measurement_unit_id')
                            ->relationship('measurementUnit', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\TextInput::make('quantity_required')
                            ->numeric()
                            ->required()
                            ->placeholder('0.0000'),
                        Forms\Components\TextInput::make('version')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\Toggle::make('is_current')
                            ->label('Current Active Version')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('menuItem.name')
                    ->label('Menu Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('itemVariant.label')
                    ->label('Variant')
                    ->placeholder('Base Item'),
                Tables\Columns\TextColumn::make('groceryItem.name')
                    ->label('Raw Ingredient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_required')
                    ->label('Qty Required')
                    ->state(fn (Recipe $record) => $record->quantity_required . ' ' . $record->measurementUnit->short_name),
                Tables\Columns\TextColumn::make('version')
                    ->numeric(),
                Tables\Columns\IconColumn::make('is_current')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('menu_item_id')
                    ->relationship('menuItem', 'name'),
                Tables\Filters\ToggledFilter::make('is_current'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
