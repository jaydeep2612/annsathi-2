<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SubscriptionsResource\Pages;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'SaaS Management';
    protected static ?string $navigationLabel = 'Subscriptions';
    protected static ?string $modelLabel = 'Subscription';
    protected static ?string $pluralModelLabel = 'Subscriptions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Status')
                    ->description('Review and update subscription status and expiration timelines')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('subscription_plan')
                            ->options([
                                'trial' => 'Trial Tier',
                                'basic' => 'Basic Tier',
                                'pro' => 'Professional Tier',
                                'enterprise' => 'Enterprise Tier',
                            ])
                            ->disabled()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Subscription / Trial Expiry'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Is Active / Not Suspended')
                            ->default(true),
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
                    ->label('Active Status'),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'edit' => Pages\EditSubscriptions::route('/{record}/edit'),
        ];
    }
}
