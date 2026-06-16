<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Services;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Domains\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierLedgerService
{
    /**
     * Record a credit entry (increases what we owe to the supplier).
     */
    public function recordCredit(
        int|Supplier $supplier,
        float $amount,
        string $referenceType = null,
        int $referenceId = null,
        string $notes = null,
        int $branchId = null
    ): SupplierLedger {
        if ($amount <= 0) {
            throw ProcurementException::invalidAmount($amount);
        }

        return DB::transaction(function () use ($supplier, $amount, $referenceType, $referenceId, $notes, $branchId) {
            $supplierModel = $supplier instanceof Supplier 
                ? $supplier 
                : Supplier::lockForUpdate()->find($supplier);

            if (!$supplierModel) {
                throw ProcurementException::supplierNotFound(is_int($supplier) ? $supplier : 0);
            }

            // Get current balance with lock to prevent race conditions
            $latestLedger = SupplierLedger::where('supplier_id', $supplierModel->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $oldBalance = $latestLedger ? (float) $latestLedger->balance_after : 0.00;
            $newBalance = $oldBalance + $amount;

            $restaurantId = $supplierModel->restaurant_id;
            $resolvedBranchId = $branchId ?? (app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null);

            return SupplierLedger::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $resolvedBranchId,
                'supplier_id' => $supplierModel->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'performed_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Record a debit entry (decreases what we owe to the supplier, e.g., on payment).
     */
    public function recordDebit(
        int|Supplier $supplier,
        float $amount,
        string $referenceType = null,
        int $referenceId = null,
        string $notes = null,
        int $branchId = null
    ): SupplierLedger {
        if ($amount <= 0) {
            throw ProcurementException::invalidAmount($amount);
        }

        return DB::transaction(function () use ($supplier, $amount, $referenceType, $referenceId, $notes, $branchId) {
            $supplierModel = $supplier instanceof Supplier 
                ? $supplier 
                : Supplier::lockForUpdate()->find($supplier);

            if (!$supplierModel) {
                throw ProcurementException::supplierNotFound(is_int($supplier) ? $supplier : 0);
            }

            // Get current balance with lock
            $latestLedger = SupplierLedger::where('supplier_id', $supplierModel->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $oldBalance = $latestLedger ? (float) $latestLedger->balance_after : 0.00;
            $newBalance = $oldBalance - $amount;

            $restaurantId = $supplierModel->restaurant_id;
            $resolvedBranchId = $branchId ?? (app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null);

            $ledger = SupplierLedger::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $resolvedBranchId,
                'supplier_id' => $supplierModel->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'performed_by' => Auth::id(),
            ]);

            app(\App\Domains\Accounting\Services\AccountingService::class)->postSupplierPayment($supplierModel, $amount);

            return $ledger;
        });
    }
}
