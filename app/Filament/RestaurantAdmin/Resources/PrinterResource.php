<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\PrinterResource\Pages;
use App\Models\Printer;
use App\Models\PrinterGroup;
use App\Models\PrinterRoute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrinterResource extends Resource
{
    protected static ?string $model = Printer::class;

    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Printers & Routing';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $modelLabel = 'Printer';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Printer Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Select::make('connection_type')
                            ->options([
                                'network' => 'Network (IP)',
                                'usb' => 'USB',
                                'bluetooth' => 'Bluetooth',
                            ])
                            ->default('network')
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->maxLength(45)
                            ->placeholder('e.g. 192.168.1.100')
                            ->required(fn ($get) => $get('connection_type') === 'network'),
                        Forms\Components\TextInput::make('port')
                            ->numeric()
                            ->default(9100)
                            ->required(fn ($get) => $get('connection_type') === 'network'),
                        Forms\Components\TextInput::make('mac_address')
                            ->label('MAC Address')
                            ->maxLength(48)
                            ->placeholder('e.g. AA:BB:CC:DD:EE:FF'),
                        Forms\Components\TextInput::make('printer_model')
                            ->label('Printer Model')
                            ->maxLength(50)
                            ->placeholder('e.g. Epson TM-T88VI'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
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
                Tables\Columns\TextColumn::make('connection_type')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address / Port')
                    ->formatStateUsing(fn ($record) => $record->connection_type === 'network' ? "{$record->ip_address}:{$record->port}" : 'N/A')
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
                Tables\Filters\SelectFilter::make('connection_type')
                    ->options([
                        'network' => 'Network',
                        'usb' => 'USB',
                        'bluetooth' => 'Bluetooth',
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
            'index' => Pages\ListPrinters::route('/'),
            'create' => Pages\CreatePrinter::route('/create'),
            'edit' => Pages\EditPrinter::route('/{record}/edit'),
        ];
    }
}
