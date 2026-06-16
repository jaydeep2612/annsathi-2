<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Domains\Procurement\Services\SupplierLedgerService;

class LedgersRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgers';
    protected static ?string $title = 'Supplier Statement & Ledgers';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('notes')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posting Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'danger' => 'debit',
                        'success' => 'credit',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Running Balance')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Source Ref')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : 'Manual')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference_id')
                    ->label('Ref ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('performer.name')
                    ->label('Recorded By')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'debit' => 'Debit (Payment)',
                        'credit' => 'Credit (Receipt)',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('record_payment')
                    ->label('Record Payment (Debit)')
                    ->icon('heroicon-o-currency-rupee')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->placeholder('0.00'),
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Enter payment details, reference ID, check/UPI number')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data) {
                        try {
                            $service = app(SupplierLedgerService::class);
                            $service->recordDebit(
                                supplier: $this->getOwnerRecord(),
                                amount: (float) $data['amount'],
                                referenceType: null,
                                referenceId: null,
                                notes: $data['notes'] ?: 'Manual supplier payment recorded',
                                branchId: app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null
                            );

                            Notification::make()
                                ->title('Payment Recorded')
                                ->body('The supplier ledger has been debited successfully.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Action Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
