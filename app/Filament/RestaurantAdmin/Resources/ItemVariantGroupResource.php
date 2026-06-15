<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\ItemVariantGroupResource\Pages;
use App\Models\ItemVariantGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItemVariantGroupResource extends Resource
{
    protected static ?string $model = ItemVariantGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Menu & Taxes';
    protected static ?string $navigationLabel = 'Modifier & Variant Groups';
    protected static ?string $modelLabel = 'Modifier Group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Group Settings')
                    ->description('Details of the variant or option group (e.g. Size, Toppings)')
                    ->schema([
                        Forms\Components\Select::make('menu_item_id')
                            ->relationship('menuItem', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(80)
                            ->placeholder('e.g., Select Pizza Size or Add Toppings'),
                        Forms\Components\Toggle::make('is_required')
                            ->label('Required Selection')
                            ->default(true),
                        Forms\Components\TextInput::make('min_select')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('max_select')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Variants / Options')
                    ->description('Define options in this modifier group')
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->maxLength(80)
                                    ->placeholder('e.g., Regular, Large, Extra Cheese'),
                                Forms\Components\Select::make('price_type')
                                    ->options([
                                        'add' => 'Add to Base Price',
                                        'subtract' => 'Subtract from Base Price',
                                        'fixed' => 'Set Fixed Price',
                                    ])
                                    ->required()
                                    ->default('add')
                                    ->native(false),
                                Forms\Components\TextInput::make('price_modifier')
                                    ->numeric()
                                    ->required()
                                    ->default(0.00),
                                Forms\Components\Toggle::make('is_available')
                                    ->label('Available')
                                    ->default(true),
                                Forms\Components\Toggle::make('affects_inventory')
                                    ->label('Track Inventory')
                                    ->default(false),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->grid(1)
                            ->defaultItems(1),
                    ]),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),
                Tables\Columns\TextColumn::make('min_select')
                    ->label('Min')
                    ->numeric(),
                Tables\Columns\TextColumn::make('max_select')
                    ->label('Max')
                    ->numeric(),
                Tables\Columns\TextColumn::make('variants_count')
                    ->label('Options')
                    ->state(fn (ItemVariantGroup $record) => $record->variants()->count() . ' options')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('menu_item_id')
                    ->relationship('menuItem', 'name'),
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
            'index' => Pages\ListItemVariantGroups::route('/'),
            'create' => Pages\CreateItemVariantGroup::route('/create'),
            'edit' => Pages\EditItemVariantGroup::route('/{record}/edit'),
        ];
    }
}
