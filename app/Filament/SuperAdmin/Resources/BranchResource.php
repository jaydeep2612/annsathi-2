<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'SaaS Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branch Settings')
                    ->description('Details of the restaurant branch location')
                    ->schema([
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('phone_no')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('upi_id')
                            ->label('UPI ID')
                            ->maxLength(50),
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
                    ->label('Restaurant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_no')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
