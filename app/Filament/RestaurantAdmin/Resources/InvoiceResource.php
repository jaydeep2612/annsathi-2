<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources;

use App\Filament\RestaurantAdmin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Invoices';
    protected static ?string $navigationGroup = 'Billing & Finance';
    protected static ?string $modelLabel = 'Invoice';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Header')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')->disabled(),
                        Forms\Components\DatePicker::make('invoice_date')->disabled(),
                        Forms\Components\TextInput::make('customer_name')->disabled(),
                        Forms\Components\TextInput::make('gstin')->label('GSTIN')->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Financial Summary')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')->numeric()->disabled(),
                        Forms\Components\TextInput::make('discount_amount')->numeric()->disabled(),
                        Forms\Components\TextInput::make('tax_amount')->label('Tax Amount')->numeric()->disabled(),
                        Forms\Components\TextInput::make('extra_charges')->label('Extra Charges')->numeric()->disabled(),
                        Forms\Components\TextInput::make('grand_total')->numeric()->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('voided_by_credit_note_id')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Voided' : 'Paid')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success'),
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
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
