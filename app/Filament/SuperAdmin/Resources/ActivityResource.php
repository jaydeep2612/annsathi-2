<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\ActivityResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System Audit Center';
    protected static ?string $navigationLabel = 'Activity Logs';
    protected static ?string $modelLabel = 'Activity Log';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Log Details')
                    ->schema([
                        Forms\Components\TextInput::make('log_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('description')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject_type')
                            ->label('Subject Model')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject_id')
                            ->label('Subject ID')
                            ->disabled(),
                        Forms\Components\TextInput::make('causer_type')
                            ->label('Caused By Model')
                            ->disabled(),
                        Forms\Components\TextInput::make('causer_id')
                            ->label('Caused By ID')
                            ->disabled(),
                        Forms\Components\KeyValue::make('properties')
                            ->label('Audit Differences')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->options([
                        'default' => 'Default',
                        'system_settings' => 'System Settings',
                        'restaurant_settings' => 'Restaurant Settings',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
