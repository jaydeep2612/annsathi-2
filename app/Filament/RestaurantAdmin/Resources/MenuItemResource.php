<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Menu & Taxes';
    protected static ?string $navigationLabel = 'Menu Items';
    protected static ?string $modelLabel = 'Menu Item';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->description('Specify name, description, and categorization')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('kitchen_station_id')
                            ->relationship('kitchenStation', 'name')
                            ->placeholder('None')
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->maxLength(1000),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Tax Engine')
                    ->description('Configure billing rates, pricing structure, and tax rules')
                    ->schema([
                        Forms\Components\TextInput::make('base_price')
                            ->numeric()
                            ->required()
                            ->placeholder('0.00'),
                        Forms\Components\Select::make('tax_group_id')
                            ->label('Tax Configuration Group')
                            ->relationship('taxGroup', 'name')
                            ->placeholder('No Tax')
                            ->preload()
                            ->searchable()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Attributes & Settings')
                    ->description('Configure availability, preparation parameters, and dietary markings')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Dietary Tag')
                            ->options([
                                'veg' => 'Vegetarian',
                                'non_veg' => 'Non-Vegetarian',
                                'egg' => 'Egg Only',
                                'beverage' => 'Beverage',
                                'dessert' => 'Dessert',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('item_nature')
                            ->label('Preparation Flow')
                            ->options([
                                'premade' => 'Premade / Stocked',
                                'readymade' => 'Readymade Package',
                                'made_to_order' => 'Made To Order',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('prep_time_minutes')
                            ->label('Est. Preparation Time (mins)')
                            ->numeric()
                            ->default(15),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TagsInput::make('allergens')
                            ->label('Allergen Warnings')
                            ->placeholder('Add warning'),
                        Forms\Components\FileUpload::make('image_path')
                            ->image()
                            ->directory('menu-items')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_available')
                            ->label('Available for Orders')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Item')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('taxGroup.name')
                    ->label('Tax Group')
                    ->placeholder('No Tax')
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Dietary')
                    ->badge()
                    ->colors([
                        'success' => 'veg',
                        'danger' => 'non_veg',
                        'warning' => 'egg',
                        'info' => 'beverage',
                    ]),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
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
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
