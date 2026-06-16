<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Promotions & Coupons';
    protected static ?string $modelLabel = 'Promotion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->description('Specify name, coupon code, and discount type')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('e.g., Summer Special 15% Off'),
                        Forms\Components\TextInput::make('code')
                            ->label('Coupon Code')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('e.g., SUMMER15')
                            ->dehydrateStateUsing(fn ($state) => $state ? strtoupper(trim($state)) : null),
                        Forms\Components\Select::make('type')
                            ->label('Discount Type')
                            ->options([
                                'flat' => 'Flat Rate Discount',
                                'percent' => 'Percentage Discount',
                                'bogo' => 'Buy One Get One (BOGO)',
                            ])
                            ->required()
                            ->native(false)
                            ->reactive(),
                    ])->columns(3),

                Forms\Components\Section::make('Discount Values & Rules')
                    ->description('Define discount value limits and minimum constraints')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label(fn (Forms\Get $get) => $get('type') === 'percent' ? 'Discount Percentage (%)' : 'Discount Amount (INR)')
                            ->numeric()
                            ->required(fn (Forms\Get $get) => in_array($get('type'), ['flat', 'percent']))
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['flat', 'percent']))
                            ->placeholder('0.00'),
                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Minimum Order Value (INR)')
                            ->numeric()
                            ->default(0.00)
                            ->required(),
                        Forms\Components\TextInput::make('max_discount_amount')
                            ->label('Max Discount Cap (INR)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'percent')
                            ->placeholder('Leave empty for no limit'),
                    ])
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['flat', 'percent']))
                    ->columns(3),

                Forms\Components\Section::make('Buy-One-Get-One (BOGO) Configuration')
                    ->description('Set up menu item conditions for BOGO promotions')
                    ->schema([
                        Forms\Components\Select::make('bogo_buy_menu_item_id')
                            ->label('If Customer Buys Item')
                            ->options(function () {
                                return MenuItem::all()->pluck('name', 'id');
                            })
                            ->required(fn (Forms\Get $get) => $get('type') === 'bogo')
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('bogo_get_menu_item_id')
                            ->label('Then Customer Gets Free Item')
                            ->options(function () {
                                return MenuItem::all()->pluck('name', 'id');
                            })
                            ->required(fn (Forms\Get $get) => $get('type') === 'bogo')
                            ->searchable()
                            ->native(false),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'bogo')
                    ->columns(2),

                Forms\Components\Section::make('Validity & Scheduling')
                    ->description('Set validity dates and active status')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->native(false),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active status')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'info' => 'flat',
                        'success' => 'percent',
                        'warning' => 'bogo',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('value')
                    ->label('Discount / Config')
                    ->formatStateUsing(function (string $state, Promotion $record): string {
                        if ($record->type === 'flat') {
                            return 'INR ' . number_format((float) $state, 2);
                        }
                        if ($record->type === 'percent') {
                            return number_format((float) $state, 0) . '%';
                        }
                        return 'BOGO';
                    }),
                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Min Order Value')
                    ->money('INR'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->label('Expiry Date'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'flat' => 'Flat Rate',
                        'percent' => 'Percentage',
                        'bogo' => 'BOGO',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
