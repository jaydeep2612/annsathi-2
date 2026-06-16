<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\JournalEntryResource\Pages;

use App\Filament\RestaurantAdmin\Resources\JournalEntryResource;
use App\Domains\Accounting\Services\AccountingService;
use App\Models\Account;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Exception;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $lines = [];
            foreach ($data['lines'] ?? [] as $line) {
                $account = Account::findOrFail($line['account_id']);
                $lines[] = [
                    'account_code' => $account->code,
                    'type' => $line['type'],
                    'amount' => (float) $line['amount'],
                ];
            }

            $service = app(AccountingService::class);
            return $service->postJournalEntry([
                'entry_date' => $data['entry_date'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'lines' => $lines,
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title('Journal Posting Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
