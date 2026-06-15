<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SystemSettingResource\Pages;
use App\Domains\Settings\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'SaaS Management';
    protected static ?string $navigationLabel = 'System Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('System Configuration')
                    ->description('Manage platform-wide settings and system-level defaults')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?SystemSetting $record) => $record !== null),
                        Forms\Components\Textarea::make('value')
                            ->required()
                            ->rows(3),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(500),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
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
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
