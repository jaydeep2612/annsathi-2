<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\GoodsReceiptResource\Pages;
use App\Models\GoodsReceipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GoodsReceiptResource extends Resource
{
    protected static ?string $model = GoodsReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Procurement';
    protected static ?string $navigationLabel = 'Goods Receipts';
    protected static ?string $modelLabel = 'Goods Receipt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Goods Receipt Metadata')
                    ->description('Verification audit details')
                    ->schema([
                        Forms\Components\Select::make('purchase_order_id')
                            ->relationship('purchaseOrder', 'po_number')
                            ->label('Purchase Order')
                            ->disabled(),
                        Forms\Components\Select::make('received_by')
                            ->relationship('receiver', 'name')
                            ->label('Received By')
                            ->disabled(),
                        Forms\Components\DatePicker::make('receipt_date')
                            ->label('Receipt Date')
                            ->disabled()
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Received Items')
                    ->description('List of items checked and updated into stock')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('grocery_item_id')
                                    ->label('Ingredient / Item')
                                    ->relationship('groceryItem', 'name')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity_received')
                                    ->label('Qty Received')
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->prefix('INR')
                                    ->disabled(),
                                Forms\Components\TextInput::make('total_cost')
                                    ->label('Total Cost')
                                    ->numeric()
                                    ->prefix('INR')
                                    ->disabled(),
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Batch #')
                                    ->disabled(),
                                Forms\Components\DatePicker::make('expiry_date')
                                    ->label('Expiry')
                                    ->disabled()
                                    ->native(false),
                                Forms\Components\TextInput::make('quality_status')
                                    ->label('Quality')
                                    ->disabled(),
                            ])
                            ->columns(7)
                            ->disabled()
                            ->addable(false)
                            ->deletable(false)
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('receipt_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
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
            'index' => Pages\ListGoodsReceipts::route('/'),
            'view' => Pages\ViewGoodsReceipt::route('/{record}'),
        ];
    }
}
