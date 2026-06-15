<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\NotificationTemplateResource\Pages;
use App\Domains\Notifications\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'SaaS Management';
    protected static ?string $navigationLabel = 'Notification Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Identification')
                    ->description('Scope and trigger for the notification template')
                    ->schema([
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->label('Restaurant (Tenant Scope)')
                            ->placeholder('Global Default (All Tenants)')
                            ->helperText('Leave empty to define a fallback template for all restaurants')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\TextInput::make('event_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., order_ready, low_stock, waiter_call')
                            ->helperText('The technical event slug triggered by the system'),
                    ])->columns(2),

                Forms\Components\Section::make('Template Content')
                    ->description('Define standard message headers and body. You can use mustache variables like {{user_name}} or {{order_id}}')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Order #{{order_id}} is Ready!'),
                        Forms\Components\Textarea::make('body')
                            ->required()
                            ->rows(5)
                            ->placeholder('e.g., Dear {{customer_name}}, your order is ready for pickup.'),
                    ])->columns(1),

                Forms\Components\Section::make('Settings & Routing')
                    ->description('Configure target channels and active state')
                    ->schema([
                        Forms\Components\Select::make('channels')
                            ->multiple()
                            ->options([
                                'in_app' => 'In App Dashboard',
                                'email' => 'Email Delivery',
                                'sms' => 'SMS Broadcast',
                                'whatsapp' => 'WhatsApp Message',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant Scope')
                    ->placeholder('Global Default')
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('channels')
                    ->badge()
                    ->separator(','),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant')
                    ->relationship('restaurant', 'name'),
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
            'index' => Pages\ListNotificationTemplates::route('/'),
            'create' => Pages\CreateNotificationTemplate::route('/create'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
