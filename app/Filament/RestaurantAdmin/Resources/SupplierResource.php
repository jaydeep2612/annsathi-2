<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\SupplierResource\Pages;
use App\Filament\RestaurantAdmin\Resources\SupplierResource\RelationManagers\LedgersRelationManager;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Procurement';
    protected static ?string $navigationLabel = 'Suppliers';
    protected static ?string $modelLabel = 'Supplier';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Supplier Information')
                    ->description('Contact and location details for the raw inventory supplier')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('e.g., Prime Dairy Distributors'),
                        Forms\Components\TextInput::make('contact_person')
                            ->maxLength(100)
                            ->placeholder('e.g., John Doe'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+91 98765 43210'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(100)
                            ->placeholder('contact@primedairy.com'),
                        Forms\Components\TextInput::make('gst_number')
                            ->label('GSTIN / Tax Number')
                            ->maxLength(20)
                            ->placeholder('22AAAAA0000A1Z5'),
                        Forms\Components\TextInput::make('payment_terms')
                            ->label('Payment Terms')
                            ->maxLength(100)
                            ->placeholder('e.g., Net 30, Cash on Delivery'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active status')
                            ->default(true),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Enter complete business address'),
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
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Outstanding Balance')
                    ->money('INR'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
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
        return [
            LedgersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
