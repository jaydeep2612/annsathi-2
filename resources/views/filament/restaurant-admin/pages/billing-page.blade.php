<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @php
            $activeOrders = $this->getActiveOrders();
            $selectedOrder = $this->selectedOrder;
        @endphp

        <!-- LEFT COLUMN: ACTIVE UNPAID ORDERS LIST (4/12 cols) -->
        <div class="lg:col-span-5 xl:col-span-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden max-h-[calc(100vh-12rem)]">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                <h3 class="font-bold text-gray-800 dark:text-white">Active Unpaid Orders</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select an active order to process checkout.</p>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                @forelse($activeOrders as $order)
                    @php
                        $tableName = $order->customerSession && $order->customerSession->sessionable
                            ? ($order->customerSession->session_type === 'table'
                                ? 'Table: ' . $order->customerSession->sessionable->name
                                : 'Room: ' . $order->customerSession->sessionable->room_number)
                            : 'Direct / Takeaway';
                        
                        $statusColors = match($order->status) {
                            'served' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
                            'ready' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300',
                            'preparing' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                        };
                        
                        $isSelected = $selectedOrderId === $order->id;
                        $cardBorder = $isSelected ? 'border-emerald-500 bg-emerald-50/10 dark:bg-emerald-950/10 ring-1 ring-emerald-500' : 'border-gray-100 dark:border-gray-700 hover:border-emerald-300';
                    @endphp

                    <div wire:click="selectOrder({{ $order->id }})" class="p-4 rounded-xl border-2 cursor-pointer transition-all duration-150 {{ $cardBorder }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded uppercase tracking-wider {{ $statusColors }}">
                                    {{ $order->status }}
                                </span>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-2">{{ $tableName }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Guest: {{ $order->customer_name ?? 'Guest' }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Order #{{ $order->id }} • {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-bold text-gray-900 dark:text-white">₹{{ number_format((float) $order->total_amount, 2) }}</span>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $order->orderItems->sum('quantity') }} items</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No active unpaid orders.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT COLUMN: BILL CHECKOUT & DETAILS (7/12 cols) -->
        <div class="lg:col-span-7 xl:col-span-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden max-h-[calc(100vh-12rem)]">
            @if(!$selectedOrder)
                <!-- PLACEHOLDER STATE -->
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-full text-emerald-600 dark:text-emerald-400 mb-4">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">POS Checkout Terminal</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mt-1">Select any unpaid order from the left pane to view items snapshot and process billing.</p>
                </div>
            @else
                <!-- ORDER BILLING DETAILS -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/30 dark:bg-gray-900/10">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                            Checkout Order #{{ $selectedOrder->id }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Server: {{ $selectedOrder->waiter->name ?? 'None' }} • Customer: {{ $selectedOrder->customer_name ?? 'Guest' }}
                        </p>
                    </div>
                    <button wire:click="$set('selectedOrderId', null)" class="text-xs font-semibold text-gray-500 hover:text-gray-800 dark:hover:text-white">
                        Deselect
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Ordered Items Grid/List -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Ordered Items</h4>
                        <div class="border border-gray-50 dark:border-gray-700/50 rounded-xl overflow-hidden divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach($selectedOrder->orderItems as $item)
                                <div class="flex items-center justify-between p-3.5 bg-gray-50/20 dark:bg-gray-900/10">
                                    <div class="space-y-0.5">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $item->item_name }}</p>
                                        @if($item->item_variant_label)
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Variant: {{ $item->item_variant_label }}</p>
                                        @endif
                                        @if($item->notes)
                                            <p class="text-xs text-amber-600 dark:text-amber-400 italic">"{{ $item->notes }}"</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-8">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }} x ₹{{ number_format((float) $item->unit_price, 2) }}</span>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">₹{{ number_format((float) $item->total_price, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bill Breakdown & Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Invoice Notes & Override fields -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Payment Details & Remarks</h4>
                            
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Transaction Ref / Card Digit (Optional)</label>
                                <input type="text" wire:model="referenceNote" placeholder="e.g. Card last 4 digits, UPI TXID"
                                       class="w-full rounded-lg text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Internal Billing Notes</label>
                                <textarea wire:model="notes" rows="2" placeholder="Manager overrides, split details..."
                                          class="w-full rounded-lg text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"></textarea>
                            </div>
                        </div>

                        <!-- Financial Summary Cards -->
                        <div class="bg-gray-50/50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-50 dark:border-gray-700 space-y-3">
                            <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Financial Summary</h4>
                            
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span>₹{{ number_format((float) $selectedOrder->subtotal, 2) }}</span>
                            </div>
                            
                            @if($selectedOrder->discount_amount > 0)
                                <div class="flex justify-between text-sm text-rose-600 dark:text-rose-400">
                                    <span>Discount ({{ $selectedOrder->discount_type === 'percent' ? $selectedOrder->discount_value . '%' : 'Flat' }})</span>
                                    <span>-₹{{ number_format((float) $selectedOrder->discount_amount, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>GST / Sales Tax ({{ number_format((float) $selectedOrder->tax_rate, 1) }}%)</span>
                                <span>₹{{ number_format((float) $selectedOrder->tax_amount, 2) }}</span>
                            </div>

                            @if($selectedOrder->extra_charges > 0)
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ $selectedOrder->extra_charges_label ?: 'Service Charge' }}</span>
                                    <span>₹{{ number_format((float) $selectedOrder->extra_charges, 2) }}</span>
                                </div>
                            @endif

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between text-base font-extrabold text-gray-900 dark:text-white">
                                <span>Grand Total</span>
                                <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format((float) $selectedOrder->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Select Grid -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Select Payment Method</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach([
                                'cash' => 'Cash Payment',
                                'upi' => 'UPI Scan',
                                'card' => 'Debit/Credit Card',
                                'room_charge' => 'Room Charge',
                                'complimentary' => 'Complimentary',
                                'other' => 'Other Method'
                            ] as $key => $label)
                                @php
                                    $isSel = $paymentMethod === $key;
                                    $methodBorder = $isSel ? 'border-emerald-500 bg-emerald-50/10 dark:bg-emerald-950/20' : 'border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900';
                                @endphp
                                <label class="flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition-all duration-150 {{ $methodBorder }}">
                                    <input type="radio" wire:model="paymentMethod" value="{{ $key }}" class="sr-only">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $label }}</span>
                                    @if($isSel)
                                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Cash Calculation (Conditionally visible if Cash is selected) -->
                    @if($paymentMethod === 'cash')
                        <div class="bg-emerald-50/30 dark:bg-emerald-950/5 p-5 rounded-2xl border border-emerald-100/30 dark:border-emerald-900/10 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 dark:text-emerald-400 mb-1">Cash Received (INR)</label>
                                <input type="number" step="0.01" min="0" wire:model.live="cashReceived"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                            </div>
                            <div class="text-left md:text-right">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Change to Return</span>
                                <h3 class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">₹{{ number_format((float) $changeAmount, 2) }}</h3>
                            </div>
                        </div>
                    @endif

                    <!-- Seating Session check-out toggle -->
                    @if($selectedOrder->customer_session_id)
                        <div class="flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/20 p-4 rounded-xl border border-gray-50 dark:border-gray-700">
                            <input type="checkbox" wire:model="releaseTable" id="releaseTableCheckbox"
                                   class="h-4.5 w-4.5 text-emerald-600 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500">
                            <label for="releaseTableCheckbox" class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                Auto-close customer session & mark table/room as Available
                            </label>
                        </div>
                    @endif
                </div>

                <!-- Footer: Actions -->
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/10 flex justify-end gap-3">
                    <button wire:click="$set('selectedOrderId', null)"
                            class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-lg transition-all duration-150">
                        Cancel Checkout
                    </button>
                    <button wire:click="processPayment"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-150">
                        Complete Payment & Generate Invoice
                    </button>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
