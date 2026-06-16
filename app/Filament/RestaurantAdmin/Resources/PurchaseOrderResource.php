<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\GroceryItem;
use App\Models\MeasurementUnit;
use App\Domains\Procurement\Services\PurchaseOrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Procurement';
    protected static ?string $navigationLabel = 'Purchase Orders';
    protected static ?string $modelLabel = 'Purchase Order';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Order Details')
                    ->description('General vendor and delivery terms')
                    ->schema([
                        Forms\Components\TextInput::make('po_number')
                            ->label('PO Number')
                            ->required()
                            ->default(fn () => 'PO-' . now()->format('YmdHis'))
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false),
                        Forms\Components\DatePicker::make('expected_delivery_date')
                            ->label('Expected Delivery Date')
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent to Supplier',
                                'partial' => 'Partially Received',
                                'received' => 'Fully Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false)
                            ->disabled(fn ($context) => $context === 'edit'), // managed by actions in edit
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Purchase Order Items')
                    ->description('Add raw grocery / ingredient items to order')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('grocery_item_id')
                                    ->label('Ingredient / Item')
                                    ->options(function () {
                                        return GroceryItem::all()->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $item = GroceryItem::find($state);
                                            if ($item) {
                                                $set('measurement_unit_id', $item->measurement_unit_id);
                                                $set('unit_price', $item->cost_per_unit ?? 0);
                                            }
                                        }
                                    }),
                                Forms\Components\Select::make('measurement_unit_id')
                                    ->label('Unit')
                                    ->relationship('measurementUnit', 'name')
                                    ->required()
                                    ->preload()
                                    ->native(false),
                                Forms\Components\TextInput::make('ordered_quantity')
                                    ->label('Ordered Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1.00)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $qty = (float) $state;
                                        $price = (float) $get('unit_price');
                                        $set('total_price', round($qty * $price, 2));
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('0.00')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $price = (float) $state;
                                        $qty = (float) $get('ordered_quantity');
                                        $set('total_price', round($qty * $price, 2));
                                    }),
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('0.00'),
                            ])
                            ->columns(5)
                            ->live()
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('total_amount_placeholder')
                            ->label('Live Order Subtotal')
                            ->content(function (Forms\Get $get) {
                                $items = $get('items') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $qty = (float) ($item['ordered_quantity'] ?? 0);
                                    $price = (float) ($item['unit_price'] ?? 0);
                                    $total += $qty * $price;
                                }
                                return 'INR ' . number_format($total, 2);
                            })
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'warning' => 'partial',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'partial' => 'Partial',
                        'received' => 'Received',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // 1. Mark as Sent Action
                Tables\Actions\Action::make('mark_sent')
                    ->label('Mark Sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (PurchaseOrder $record) => $record->status === 'draft')
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => 'sent']);
                        Notification::make()
                            ->title('PO Marked as Sent')
                            ->success()
                            ->send();
                    }),

                // 2. Receive Goods Action
                Tables\Actions\Action::make('receive_goods')
                    ->label('Receive Goods')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $record) => in_array($record->status, ['sent', 'partial']))
                    ->mountUsing(function (Forms\ComponentContainer $form, PurchaseOrder $record) {
                        $items = $record->items->map(function ($item) {
                            return [
                                'purchase_order_item_id' => $item->id,
                                'grocery_item_name' => $item->groceryItem->name,
                                'ordered_quantity' => $item->ordered_quantity,
                                'received_quantity' => $item->received_quantity,
                                'quantity_received' => max(0, $item->ordered_quantity - $item->received_quantity),
                                'unit_cost' => $item->unit_price,
                                'batch_number' => 'BATCH-' . $record->po_number . '-' . now()->format('ymd'),
                                'expiry_date' => null,
                            ];
                        })->toArray();

                        $form->fill([
                            'receipt_date' => now()->toDateString(),
                            'notes' => 'Received goods against PO: ' . $record->po_number,
                            'items' => $items,
                        ]);
                    })
                    ->form([
                        Forms\Components\DatePicker::make('receipt_date')
                            ->label('Receipt Date')
                            ->required()
                            ->default(now()->toDateString())
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Receipt Notes'),
                        Forms\Components\Repeater::make('items')
                            ->label('Items to Receive')
                            ->schema([
                                Forms\Components\Hidden::make('purchase_order_item_id'),
                                Forms\Components\TextInput::make('grocery_item_name')
                                    ->label('Item')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('ordered_quantity')
                                    ->label('Ordered')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('received_quantity')
                                    ->label('Received So Far')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('quantity_received')
                                    ->label('Qty receiving')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Batch #')
                                    ->placeholder('Autogenerated if blank'),
                                Forms\Components\DatePicker::make('expiry_date')
                                    ->label('Expiry')
                                    ->native(false),
                            ])
                            ->columns(7)
                            ->addable(false)
                            ->deletable(false)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, PurchaseOrder $record) {
                        try {
                            $service = app(PurchaseOrderService::class);
                            $service->receiveGoods($record, $data);

                            Notification::make()
                                ->title('Goods Received')
                                ->body('The inventory has been updated and supplier ledger credited.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Receipt Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
