<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Pages;

use Filament\Pages\Page;
use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Services\ShiftService;
use Filament\Notifications\Notification;

class CashDrawerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Cash Drawer & Shifts';
    protected static ?string $navigationGroup = 'Billing & Finance';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->is_super_admin || auth()->user()->hasPermissionTo('manage_shifts');
    }

    protected static string $view = 'filament.restaurant-admin.pages.cash-drawer-page';

    // Open Shift Form Properties
    public $openingBalance = 0.00;
    public $shiftName = '';
    public $shiftType = 'custom';
    public $notes = '';

    // Cash Movement Form Properties
    public $movementType = 'cash_in';
    public $movementAmount = 0.00;
    public $movementReason = '';

    // Close Shift Form Properties
    public $closingBalance = 0.00;
    public $closingNotes = '';

    public function mount(): void
    {
        $this->resetShiftForm();
    }

    public function resetShiftForm(): void
    {
        $this->shiftName = 'Shift-' . now()->format('Ymd-Hi');
        $this->openingBalance = 0.00;
        $this->shiftType = 'custom';
        $this->notes = '';

        $this->movementType = 'cash_in';
        $this->movementAmount = 0.00;
        $this->movementReason = '';

        $this->closingBalance = 0.00;
        $this->closingNotes = '';
    }

    public function getActiveShift()
    {
        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        return Shift::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();
    }

    public function getActiveDrawer()
    {
        $shift = $this->getActiveShift();
        if (!$shift) {
            return null;
        }

        return CashDrawer::where('shift_id', $shift->id)
            ->where('status', 'open')
            ->first();
    }

    public function getCashMovements()
    {
        $drawer = $this->getActiveDrawer();
        if (!$drawer) {
            return collect();
        }

        return CashMovement::where('cash_drawer_id', $drawer->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function openShift(): void
    {
        $this->validate([
            'shiftName' => 'required|string|max:100',
            'openingBalance' => 'required|numeric|min:0',
            'shiftType' => 'required|in:morning,afternoon,evening,night,custom',
        ]);

        try {
            $shiftService = app(ShiftService::class);
            $shiftService->openShift([
                'name' => $this->shiftName,
                'shift_type' => $this->shiftType,
                'opening_balance' => (float) $this->openingBalance,
                'notes' => $this->notes,
                'started_by' => auth()->id(),
            ]);

            Notification::make()
                ->title('Shift Opened')
                ->body('A new shift and cash drawer have been successfully initialized.')
                ->success()
                ->send();

            $this->resetShiftForm();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Opening Shift')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function recordMovement(): void
    {
        $this->validate([
            'movementAmount' => 'required|numeric|gt:0',
            'movementReason' => 'required|string|max:255',
            'movementType' => 'required|in:cash_in,cash_out',
        ]);

        $shift = $this->getActiveShift();
        if (!$shift) {
            Notification::make()
                ->title('No Active Shift')
                ->body('You must open a shift before recording cash movements.')
                ->danger()
                ->send();
            return;
        }

        try {
            $shiftService = app(ShiftService::class);
            $shiftService->recordCashMovement(
                $shift->id,
                $this->movementType,
                (float) $this->movementAmount,
                $this->movementReason
            );

            Notification::make()
                ->title('Cash Movement Recorded')
                ->success()
                ->send();

            $this->movementAmount = 0.00;
            $this->movementReason = '';
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Recording Movement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeShift(): void
    {
        $this->validate([
            'closingBalance' => 'required|numeric|min:0',
        ]);

        $shift = $this->getActiveShift();
        if (!$shift) {
            Notification::make()
                ->title('No Active Shift')
                ->danger()
                ->send();
            return;
        }

        try {
            $shiftService = app(ShiftService::class);
            $shiftService->closeShift($shift->id, [
                'ended_by' => auth()->id(),
                'closing_balance' => (float) $this->closingBalance,
                'notes' => $this->closingNotes,
            ]);

            Notification::make()
                ->title('Shift Closed')
                ->body('Shift closed and drawer reconciled successfully.')
                ->success()
                ->send();

            $this->resetShiftForm();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Closing Shift')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
