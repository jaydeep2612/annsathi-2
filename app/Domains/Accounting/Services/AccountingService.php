<?php

declare(strict_types=1);

namespace App\Domains\Accounting\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountingService
{
    /**
     * Ensure standard default chart of accounts exists for the current tenant.
     */
    public function ensureDefaultAccountsExist(): void
    {
        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        $defaults = [
            ['code' => '1010', 'name' => 'Cash & Bank', 'type' => 'asset'],
            ['code' => '1210', 'name' => 'Inventory Asset', 'type' => 'asset'],
            ['code' => '2010', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '4010', 'name' => 'Sales Revenue', 'type' => 'revenue'],
        ];

        foreach ($defaults as $default) {
            Account::firstOrCreate(
                [
                    'restaurant_id' => $restaurantId,
                    'code' => $default['code'],
                ],
                [
                    'branch_id' => $branchId,
                    'name' => $default['name'],
                    'type' => $default['type'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Post a Journal Entry asserting debits equals credits.
     */
    public function postJournalEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            $this->ensureDefaultAccountsExist();

            // Validate debits vs credits
            $totalDebits = 0.0;
            $totalCredits = 0.0;
            $linesData = $data['lines'] ?? [];

            if (empty($linesData)) {
                throw new Exception("Journal entry must have at least one line.");
            }

            $resolvedLines = [];

            foreach ($linesData as $line) {
                $code = $line['account_code'];
                $amount = (float) $line['amount'];
                $type = $line['type']; // debit, credit

                $account = Account::where('restaurant_id', $restaurantId)
                    ->where('code', $code)
                    ->firstOrFail();

                if ($type === 'debit') {
                    $totalDebits += $amount;
                } elseif ($type === 'credit') {
                    $totalCredits += $amount;
                } else {
                    throw new Exception("Invalid journal entry line type: {$type}");
                }

                $resolvedLines[] = [
                    'account_id' => $account->id,
                    'type' => $type,
                    'amount' => $amount,
                ];
            }

            // Enforce equal balance
            if (abs($totalDebits - $totalCredits) > 0.001) {
                throw new Exception("Journal entry out of balance. Total Debits: {$totalDebits}, Total Credits: {$totalCredits}");
            }

            // Generate entry number
            $lastEntry = JournalEntry::where('restaurant_id', $restaurantId)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = $lastEntry ? $lastEntry->id + 1 : 1;
            $entryNumber = 'JE-' . now()->format('Ymd') . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            // Create entry header
            $entry = JournalEntry::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'entry_number' => $entryNumber,
                'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'posted_by' => auth()->id(),
            ]);

            // Create lines
            foreach ($resolvedLines as $resolvedLine) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $resolvedLine['account_id'],
                    'type' => $resolvedLine['type'],
                    'amount' => $resolvedLine['amount'],
                ]);
            }

            return $entry;
        });
    }

    /**
     * Post sales revenue when an invoice is generated.
     * Debit Cash & Bank (1010), Credit Sales Revenue (4010).
     */
    public function postInvoice(Invoice $invoice): JournalEntry
    {
        $amount = (float) $invoice->grand_total;

        return $this->postJournalEntry([
            'reference' => 'Invoice #' . $invoice->invoice_number,
            'description' => 'Sales Revenue registration for Invoice ' . $invoice->invoice_number,
            'entry_date' => $invoice->invoice_date,
            'lines' => [
                [
                    'account_code' => '1010', // Cash & Bank
                    'type' => 'debit',
                    'amount' => $amount,
                ],
                [
                    'account_code' => '4010', // Sales Revenue
                    'type' => 'credit',
                    'amount' => $amount,
                ],
            ],
        ]);
    }

    /**
     * Post stock intake when goods are received from a supplier.
     * Debit Inventory Asset (1210), Credit Accounts Payable (2010).
     */
    public function postGoodsReceipt(GoodsReceipt $receipt): JournalEntry
    {
        $receipt->loadMissing('items');
        $amount = (float) $receipt->items->sum('total_cost');

        return $this->postJournalEntry([
            'reference' => 'GRN #' . $receipt->id,
            'description' => 'Inventory intake registration for Goods Receipt #' . $receipt->id,
            'entry_date' => $receipt->receipt_date ? $receipt->receipt_date->toDateString() : now()->toDateString(),
            'lines' => [
                [
                    'account_code' => '1210', // Inventory Asset
                    'type' => 'debit',
                    'amount' => $amount,
                ],
                [
                    'account_code' => '2010', // Accounts Payable
                    'type' => 'credit',
                    'amount' => $amount,
                ],
            ],
        ]);
    }

    /**
     * Post supplier payment.
     * Debit Accounts Payable (2010), Credit Cash & Bank (1010).
     */
    public function postSupplierPayment(Supplier $supplier, float $amount): JournalEntry
    {
        return $this->postJournalEntry([
            'reference' => 'Payment - ' . $supplier->name,
            'description' => 'Payment settlement to Supplier ' . $supplier->name,
            'entry_date' => now()->toDateString(),
            'lines' => [
                [
                    'account_code' => '2010', // Accounts Payable
                    'type' => 'debit',
                    'amount' => $amount,
                ],
                [
                    'account_code' => '1010', // Cash & Bank
                    'type' => 'credit',
                    'amount' => $amount,
                ],
            ],
        ]);
    }
}
