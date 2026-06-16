<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $activeShift = $this->getActiveShift();
            $activeDrawer = $this->getActiveDrawer();
        @endphp

        @if(!$activeShift)
            <!-- NO ACTIVE SHIFT: OPEN SHIFT UI -->
            <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white">
                    <h3 class="text-xl font-extrabold tracking-tight">Open a New Shift</h3>
                    <p class="text-xs text-emerald-100 mt-1">Start daily restaurant operations, track cash drawers, and manage staff schedules.</p>
                </div>

                <form wire:submit.prevent="openShift" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Shift Name *</label>
                            <input type="text" wire:model="shiftName" placeholder="e.g. Morning Shift" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Shift Type *</label>
                            <select wire:model="shiftType" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="evening">Evening</option>
                                <option value="night">Night</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Opening Cash Balance (INR) *</label>
                        <input type="number" step="0.01" min="0" wire:model="openingBalance" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Specify initial physical float cash in drawer.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Shift Notes</label>
                        <textarea wire:model="notes" rows="3" placeholder="Add details like staff list, tasks, or opening balance remarks..."
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"></textarea>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg shadow-sm transition-all duration-150">
                            Initialize & Open Shift
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- ACTIVE SHIFT UI -->
            @php
                $movements = $this->getCashMovements();
                $cashIn = $movements->where('type', 'cash_in')->sum('amount');
                $cashOut = $movements->where('type', 'cash_out')->sum('amount');
                $expectedBalance = $activeDrawer->opening_balance + $cashIn - $cashOut;
            @endphp

            <!-- Summary Bar -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 rounded-2xl shadow-sm text-white border border-emerald-400/20">
                    <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Active Shift</span>
                    <h3 class="text-xl font-bold mt-1 truncate">{{ $activeShift->name }}</h3>
                    <p class="text-xs text-emerald-100 mt-2">Started: {{ $activeShift->start_time->format('H:i | M d') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Opening Float</span>
                    <h3 class="text-2xl font-bold mt-1 text-gray-800 dark:text-white">₹{{ number_format((float) $activeDrawer->opening_balance, 2) }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">By: {{ $activeDrawer->opener->name ?? 'System' }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Net Transactions</span>
                    <h3 class="text-2xl font-bold mt-1 text-gray-800 dark:text-white">
                        +₹{{ number_format((float) $cashIn, 2) }} / -₹{{ number_format((float) $cashOut, 2) }}
                    </h3>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-medium">Net: ₹{{ number_format((float) ($cashIn - $cashOut), 2) }}</p>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-950/20 p-5 rounded-2xl shadow-sm border border-indigo-100 dark:border-indigo-900/30">
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Expected Balance</span>
                    <h3 class="text-2xl font-bold mt-1 text-indigo-800 dark:text-indigo-400">₹{{ number_format((float) $expectedBalance, 2) }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Computed live drawer balance</p>
                </div>
            </div>

            <!-- Petty Cash / Operations Split -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Record Cash Movement -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between">
                    <div>
                        <div class="border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                            <h4 class="text-lg font-bold text-gray-800 dark:text-white">Record Petty Cash Transaction</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manually record any cash cash-ins or cash-outs from drawer.</p>
                        </div>

                        <form wire:submit.prevent="recordMovement" class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Transaction Type *</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center justify-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition {{ $movementType === 'cash_in' ? 'bg-emerald-50/55 dark:bg-emerald-950/20 border-emerald-500' : '' }}">
                                        <input type="radio" wire:model="movementType" value="cash_in" class="sr-only">
                                        <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Cash In (Revenue/Float)</span>
                                    </label>
                                    <label class="flex items-center justify-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition {{ $movementType === 'cash_out' ? 'bg-rose-50/55 dark:bg-rose-950/20 border-rose-500' : '' }}">
                                        <input type="radio" wire:model="movementType" value="cash_out" class="sr-only">
                                        <span class="text-sm font-semibold text-rose-700 dark:text-rose-400">Cash Out (Expense)</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount (INR) *</label>
                                <input type="number" step="0.01" min="0.01" wire:model="movementAmount" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Reason / Description *</label>
                                <input type="text" wire:model="movementReason" placeholder="e.g. Purchased milk packet, tea stall payment" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 bg-gray-900 dark:bg-gray-700 text-white hover:bg-gray-800 dark:hover:bg-gray-600 font-semibold rounded-lg shadow-sm transition-all duration-150 mt-3">
                                Record Transaction
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Cash Drawer Log Ledger -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between">
                    <div>
                        <div class="border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                            <h4 class="text-lg font-bold text-gray-800 dark:text-white">Drawer Activity Log</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Chronological list of cash activities in current shift.</p>
                        </div>

                        <div class="max-h-[340px] overflow-y-auto space-y-2 pr-1">
                            @forelse($movements as $m)
                                @php
                                    $isAdd = in_array($m->type, ['cash_in', 'opening']);
                                    $amountColor = $isAdd ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                                    $typeBadge = match($m->type) {
                                        'opening' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'closing' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'cash_in' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
                                        'cash_out' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded uppercase tracking-wider {{ $typeBadge }}">
                                                {{ $m->type === 'opening' ? 'opening float' : $m->type }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                                                {{ $m->created_at ? $m->created_at->format('H:i') : '' }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $m->reason }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Recorded by: {{ $m->operator->name ?? 'System' }}</p>
                                    </div>
                                    <span class="text-base font-bold {{ $amountColor }}">
                                        {{ $isAdd ? '+' : '-' }}₹{{ number_format((float) $m->amount, 2) }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No cash movements recorded yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Close Shift & Reconcile -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-900/60 p-5 border-b border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">Shift Reconcilation & Closeout</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reconcile the physical cash in drawer with expected system calculations.</p>
                </div>

                <form wire:submit.prevent="closeShift" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <div class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700 space-y-2">
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wider">System Expected Balance</span>
                            <div class="text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format((float) $expectedBalance, 2) }}</div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Based on opening float, cash inputs, and transactions recorded.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Physical Cash Counted *</label>
                            <input type="number" step="0.01" min="0" wire:model="closingBalance" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Specify actual physical currency amount present inside the drawer.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Closing Remarks / Notes</label>
                            <textarea wire:model="closingNotes" rows="2" placeholder="Mention reasons for any variance or shifts summaries..."
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"></textarea>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-lg shadow-sm transition-all duration-150">
                            Submit Reconcilation & Close Shift
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-filament-panels::page>
