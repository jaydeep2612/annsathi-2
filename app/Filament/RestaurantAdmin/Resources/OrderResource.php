<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\CustomerSession;
use App\Models\Customer;
use App\Models\User;
use App\Models\MenuItem;
use App\Models\ItemVariant;
use App\Services\OrderService;
use App\Domains\Printing\Services\PrinterRoutingService;
use App\Services\KitchenRoutingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $navigationGroup = 'POS & Orders';
    protected static ?string $modelLabel = 'Order';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Setup')
                    ->schema([
                        Forms\Components\Select::make('customer_session_id')
                            ->label('Table Session')
                            ->options(CustomerSession::where('status', 'active')->pluck('session_token', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $session = CustomerSession::find($state);
                                    if ($session) {
                                        $set('customer_name', $session->customer_name);
                                        $set('service_type', $session->session_type === 'table' ? 'dine_in' : 'parcel');
                                    }
                                }
                            }),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set('customer_name', $customer->name);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('service_type')
                            ->options([
                                'dine_in' => 'Dine In',
                                'room_service' => 'Room Service',
                                'parcel' => 'Takeaway / Parcel',
                                'manual' => 'Manual',
                            ])
                            ->default('dine_in')
                            ->required(),
                        Forms\Components\Select::make('assigned_waiter_id')
                            ->label('Assigned Waiter')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('customer_name')
                            ->maxLength(100),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'served' => 'Served',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->schema([
                                Forms\Components\Select::make('menu_item_id')
                                    ->label('Menu Item')
                                    ->options(MenuItem::where('is_available', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $set('selected_variant_id', null);
                                        if ($state) {
                                            $item = MenuItem::find($state);
                                            if ($item) {
                                                $set('unit_price', $item->base_price);
                                            }
                                        }
                                    }),
                                Forms\Components\Select::make('selected_variant_id')
                                    ->label('Variant')
                                    ->options(function (Forms\Get $get) {
                                        $itemId = $get('menu_item_id');
                                        if (!$itemId) {
                                            return [];
                                        }
                                        return ItemVariant::where('menu_item_id', $itemId)->pluck('label', 'id');
                                    })
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $itemId = $get('menu_item_id');
                                        if ($itemId) {
                                            $item = MenuItem::find($itemId);
                                            $price = $item->base_price;
                                            if ($state) {
                                                $variant = ItemVariant::find($state);
                                                if ($variant) {
                                                    $price += $variant->price_modifier;
                                                }
                                            }
                                            $set('unit_price', $price);
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('notes')
                                    ->maxLength(100)
                                    ->placeholder('e.g. No onion'),
                            ])->columns(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'info',
                        'preparing' => 'warning',
                        'ready' => 'primary',
                        'served' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        default => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('H:i')
                    ->label('Time')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'served' => 'Served',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        app(OrderService::class)->confirmOrder($record->id);
                        
                        // Send KOT to kitchen and print
                        app(KitchenRoutingService::class)->routeOrderToKitchen($record);
                        app(PrinterRoutingService::class)->routeOrderKOT($record);

                        Notification::make()
                            ->title('Order Confirmed & Sent to Kitchen')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('print_receipt')
                    ->label('Receipt')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->action(function ($record) {
                        app(PrinterRoutingService::class)->routeOrderReceipt($record);
                        Notification::make()
                            ->title('Receipt sent to billing printer')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
