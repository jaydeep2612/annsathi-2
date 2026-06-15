<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\PlansResource\Pages;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlansResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'SaaS Management';
    protected static ?string $navigationLabel = 'Plans & Quotas';
    protected static ?string $modelLabel = 'Plan & Quotas';
    protected static ?string $pluralModelLabel = 'Plans & Quotas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Organization')
                    ->description('Scope configuration')
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
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Quota Limits')
                    ->description('Configure structural constraints for this tenant')
                    ->schema([
                        Forms\Components\TextInput::make('max_branches')
                            ->label('Max Branches')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('user_limits')
                            ->label('Max Users/Staff')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('table_limits')
                            ->label('Max Tables')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('rooms_limit')
                            ->label('Max Rooms')
                            ->numeric()
                            ->required(),
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
                Tables\Columns\TextColumn::make('max_branches')
                    ->label('Max Branches')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_limits')
                    ->label('Max Users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('table_limits')
                    ->label('Max Tables')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rooms_limit')
                    ->label('Max Rooms')
                    ->sortable(),
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
            'index' => Pages\ListPlans::route('/'),
            'edit' => Pages\EditPlans::route('/{record}/edit'),
        ];
    }
}
