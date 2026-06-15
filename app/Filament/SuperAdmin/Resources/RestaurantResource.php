<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\RestaurantResource\Pages;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RestaurantResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'SaaS Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->description('Primary details of the restaurant tenant')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\FileUpload::make('logo_path')
                            ->image()
                            ->directory('restaurant-logos'),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('phone_no')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('gst_no')
                            ->label('GST Number')
                            ->maxLength(15),
                        Forms\Components\TextInput::make('upi_id')
                            ->label('UPI ID')
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Subscription & Quota Management')
                    ->description('Set active tier constraints and service limitations')
                    ->schema([
                        Forms\Components\Select::make('subscription_plan')
                            ->options([
                                'trial' => 'Trial Tier',
                                'basic' => 'Basic Tier',
                                'pro' => 'Professional Tier',
                                'enterprise' => 'Enterprise Tier',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('trial_ends_at'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('user_limits')
                                    ->numeric()
                                    ->default(5)
                                    ->required(),
                                Forms\Components\TextInput::make('table_limits')
                                    ->numeric()
                                    ->default(10)
                                    ->required(),
                                Forms\Components\TextInput::make('rooms_limit')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Forms\Components\TextInput::make('max_branches')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ]),
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
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('subscription_plan')
                    ->badge()
                    ->colors([
                        'gray' => 'trial',
                        'info' => 'basic',
                        'success' => 'pro',
                        'warning' => 'enterprise',
                    ])
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('max_branches')
                    ->label('Max Branches')
                    ->numeric(),
                Tables\Columns\TextColumn::make('user_limits')
                    ->label('User Limit')
                    ->numeric(),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_plan')
                    ->options([
                        'trial' => 'Trial',
                        'basic' => 'Basic',
                        'pro' => 'Pro',
                        'enterprise' => 'Enterprise',
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
            'index' => Pages\ListRestaurants::route('/'),
            'create' => Pages\CreateRestaurant::route('/create'),
            'edit' => Pages\EditRestaurant::route('/{record}/edit'),
        ];
    }
}
