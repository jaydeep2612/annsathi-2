<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class LoyaltyTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyTransactions';
    protected static ?string $title = 'Loyalty Points Statement';

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
                        'success' => 'earn',
                        'danger' => 'redeem',
                        'warning' => 'adjustment',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('points')
                    ->label('Points Change')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? '+' : '') . $state),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->placeholder('N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Description')
                    ->searchable()
                    ->limit(60),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'earn' => 'Earned',
                        'redeem' => 'Redeemed',
                        'adjustment' => 'Adjustment',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('adjust_points')
                    ->label('Adjust Points')
                    ->icon('heroicon-o-plus-minus')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('points')
                            ->integer()
                            ->required()
                            ->label('Points Difference')
                            ->placeholder('e.g., 50 or -20'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Adjustment Reason')
                            ->required()
                            ->placeholder('Explain the reason for manual adjustment'),
                    ])
                    ->action(function (array $data) {
                        try {
                            DB::transaction(function () use ($data) {
                                $customer = $this->getOwnerRecord();
                                $points = (int) $data['points'];

                                $newPoints = max(0, $customer->loyalty_points + $points);
                                $actualChange = $newPoints - $customer->loyalty_points;

                                $customer->update([
                                    'loyalty_points' => $newPoints
                                ]);

                                \App\Models\LoyaltyTransaction::create([
                                    'restaurant_id' => $customer->restaurant_id,
                                    'customer_id' => $customer->id,
                                    'type' => 'adjustment',
                                    'points' => $actualChange,
                                    'notes' => $data['notes'],
                                ]);
                            });

                            Notification::make()
                                ->title('Loyalty Points Adjusted')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Adjustment Failed')
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
