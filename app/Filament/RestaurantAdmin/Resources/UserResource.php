<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Staff Directory';
    protected static ?string $modelLabel = 'Staff Member';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->description('Personal details and credentials')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->placeholder('Leave blank to keep current password'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Access & Scopes')
                    ->description('Assign user roles and branch permissions')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('branch_id')
                            ->label('Primary Branch')
                            ->relationship('currentBranch', 'name')
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('branches')
                            ->label('Allowed Branches')
                            ->multiple()
                            ->relationship('branches', 'name')
                            ->preload()
                            ->native(false),
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
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Roles'),
                Tables\Columns\TextColumn::make('currentBranch.name')
                    ->label('Primary Branch'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Primary Branch')
                    ->relationship('currentBranch', 'name'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
