<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class ShiftService
{
    /**
     * Open a new shift and cash drawer for the branch.
     */
    public function openShift(array $data): Shift
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            // 1. Check for active shift
            $activeShift = Shift::where('restaurant_id', $restaurantId)
                ->where('branch_id', $branchId)
                ->where('status', 'open')
                ->first();

            if ($activeShift) {
                throw new Exception("A shift is already open for this branch.");
            }

            $startedBy = $data['started_by'] ?? auth()->id();
            $openingBalance = $data['opening_balance'] ?? 0.00;

            // 2. Create Shift
            $shift = Shift::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'name' => $data['name'] ?? 'Shift-' . now()->format('Ymd-Hi'),
                'shift_type' => $data['shift_type'] ?? 'custom',
                'started_by' => $startedBy,
                'start_time' => now(),
                'status' => 'open',
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. Create Cash Drawer
            $drawer = CashDrawer::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'shift_id' => $shift->id,
                'opened_by' => $startedBy,
                'opening_balance' => $openingBalance,
                'expected_closing_balance' => $openingBalance,
                'status' => 'open',
                'opened_at' => now(),
            ]);

            // 4. Record Opening Cash Movement
            CashMovement::create([
                'cash_drawer_id' => $drawer->id,
                'restaurant_id' => $restaurantId,
                'type' => 'opening',
                'amount' => $openingBalance,
                'reason' => 'Initial drawer setup',
                'recorded_by' => $startedBy,
                'created_at' => now(),
            ]);

            event(new \App\Events\ShiftOpened($shift));

            return $shift;
        });
    }

    /**
     * Close an active shift and reconcile cash drawer balances.
     */
    public function closeShift(int $shiftId, array $data): Shift
    {
        return DB::transaction(function () use ($shiftId, $data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');
            $endedBy = $data['ended_by'] ?? auth()->id();
            $closingBalance = $data['closing_balance'] ?? 0.00;

            $shift = Shift::lockForUpdate()->findOrFail($shiftId);
            if ($shift->status !== 'open') {
                throw new Exception("Cannot close a shift that is not open.");
            }

            $drawer = CashDrawer::where('shift_id', $shift->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$drawer) {
                throw new Exception("No open cash drawer found for this shift.");
            }

            // Calculate expected balance: opening_balance + cash_in - cash_out
            $cashIn = CashMovement::where('cash_drawer_id', $drawer->id)
                ->where('type', 'cash_in')
                ->sum('amount');

            $cashOut = CashMovement::where('cash_drawer_id', $drawer->id)
                ->where('type', 'cash_out')
                ->sum('amount');

            $expectedBalance = $drawer->opening_balance + $cashIn - $cashOut;
            $variance = $closingBalance - $expectedBalance;

            // Update Cash Drawer
            $drawer->update([
                'closed_by' => $endedBy,
                'closing_balance' => $closingBalance,
                'expected_closing_balance' => $expectedBalance,
                'variance' => $variance,
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            // Record Closing Cash Movement
            CashMovement::create([
                'cash_drawer_id' => $drawer->id,
                'restaurant_id' => $restaurantId,
                'type' => 'closing',
                'amount' => $closingBalance,
                'reason' => 'Shift drawer reconciliation',
                'recorded_by' => $endedBy,
                'created_at' => now(),
            ]);

            // Update Shift
            $shift->update([
                'ended_by' => $endedBy,
                'end_time' => now(),
                'status' => 'closed',
                'notes' => $data['notes'] ?? $shift->notes,
            ]);

            event(new \App\Events\ShiftClosed($shift));

            return $shift;
        });
    }

    /**
     * Record a manual cash movement (in/out) in the active drawer.
     */
    public function recordCashMovement(int $shiftId, string $type, float $amount, string $reason): CashMovement
    {
        return DB::transaction(function () use ($shiftId, $type, $amount, $reason) {
            if (!in_array($type, ['cash_in', 'cash_out'])) {
                throw new InvalidArgumentException("Invalid cash movement type.");
            }

            $shift = Shift::findOrFail($shiftId);
            if ($shift->status !== 'open') {
                throw new Exception("Cannot record cash movement on a closed shift.");
            }

            $drawer = CashDrawer::where('shift_id', $shift->id)
                ->where('status', 'open')
                ->first();

            if (!$drawer) {
                throw new Exception("No active cash drawer found.");
            }

            $movement = CashMovement::create([
                'cash_drawer_id' => $drawer->id,
                'restaurant_id' => $shift->restaurant_id,
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'recorded_by' => auth()->id(),
                'created_at' => now(),
            ]);

            return $movement;
        });
    }
}
