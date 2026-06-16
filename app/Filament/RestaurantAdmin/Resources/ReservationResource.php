<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Customer;
use App\Domains\Reservations\Services\ReservationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Reservations';
    protected static ?string $navigationGroup = 'Seating & Reservations';
    protected static ?string $modelLabel = 'Reservation';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Guest Details')
                    ->description('Select customer or enter details manually')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Select Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set('customer_name', $customer->name);
                                        $set('customer_phone', $customer->phone);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('customer_phone')
                            ->required()
                            ->maxLength(15)
                            ->tel(),
                    ])->columns(3),

                Forms\Components\Section::make('Booking Details')
                    ->schema([
                        Forms\Components\Select::make('restaurant_table_id')
                            ->label('Table')
                            ->options(RestaurantTable::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('reservation_time')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->default(now()->addHour()),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->required()
                            ->numeric()
                            ->default(120),
                        Forms\Components\TextInput::make('pax_count')
                            ->required()
                            ->numeric()
                            ->default(2)
                            ->minValue(1),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'seated' => 'Seated',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('restaurantTable.name')
                    ->label('Table')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_time')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pax_count')
                    ->label('Pax')
                    ->numeric(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'warning',
                        'seated' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'seated' => 'Seated',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        app(ReservationService::class)->confirmReservation($record->id);
                        Notification::make()
                            ->title('Reservation Confirmed')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('seat')
                    ->color('info')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->action(function ($record) {
                        try {
                            app(ReservationService::class)->seatReservation($record->id);
                            Notification::make()
                                ->title('Reservation Seated')
                                ->body("Table is now occupied and customer session is active.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Seating Reservation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->action(function ($record) {
                        app(ReservationService::class)->cancelReservation($record->id);
                        Notification::make()
                            ->title('Reservation Cancelled')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
