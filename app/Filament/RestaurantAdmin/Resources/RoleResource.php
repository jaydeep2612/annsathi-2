<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Staff Roles';
    protected static ?string $modelLabel = 'Role';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Details')
                    ->description('Set role name and map permissions')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('guard_name')
                            ->default('web')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('permissions')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->native(false)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('guard_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->badge()
                    ->label('Permissions')
                    ->limitList(3),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
