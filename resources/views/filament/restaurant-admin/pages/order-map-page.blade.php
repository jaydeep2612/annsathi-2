<x-filament-panels::page>
    <div class="flex flex-col gap-6">

        @php
            $orders = $this->getActiveOrders();
            $statuses = [
                'pending' => ['label' => 'Pending', 'color' => 'gray', 'border' => 'border-gray-200 dark:border-gray-700'],
                'confirmed' => ['label' => 'Confirmed', 'color' => 'blue', 'border' => 'border-blue-200 dark:border-blue-900/30'],
                'preparing' => ['label' => 'Preparing', 'color' => 'amber', 'border' => 'border-amber-200 dark:border-amber-900/30'],
                'ready' => ['label' => 'Ready to Serve', 'color' => 'purple', 'border' => 'border-purple-200 dark:border-purple-900/30'],
                'served' => ['label' => 'Served', 'color' => 'emerald', 'border' => 'border-emerald-200 dark:border-emerald-900/30'],
            ];
        @endphp

        <!-- Kanban Board columns -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start min-h-[500px]">
            @foreach($statuses as $statusKey => $config)
                @php
                    $statusOrders = $orders->where('status', $statusKey);
                @endphp

                <div class="bg-gray-50/50 dark:bg-gray-900/40 rounded-xl p-4 border border-gray-100 dark:border-gray-800 flex flex-col gap-3 min-h-[450px]">
                    <!-- Column Header -->
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-gray-800/80 mb-2">
                        <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-{{ $config['color'] }}-500"></span>
                            {{ $config['label'] }}
                        </h4>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-200/60 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            {{ $statusOrders->count() }}
                        </span>
                    </div>

                    <!-- Column Cards -->
                    <div class="flex flex-col gap-3 overflow-y-auto max-h-[600px] pr-1">
                        @forelse($statusOrders as $order)
                            @php
                                $tableName = 'Takeaway';
                                if ($order->customerSession && $order->customerSession->sessionable) {
                                    $tableName = $order->customerSession->sessionable->name;
                                }
                            @endphp

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ $config['border'] }} shadow-sm flex flex-col gap-3 transition duration-150 hover:shadow-md">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">#ORD-{{ $order->id }}</span>
                                        <h5 class="font-bold text-sm text-gray-800 dark:text-gray-200 mt-0.5">{{ $tableName }}</h5>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</span>
                                </div>

                                <div class="text-xs text-gray-600 dark:text-gray-400 flex flex-col gap-1">
                                    <div>Guest: <span class="font-medium text-gray-800 dark:text-gray-200">{{ $order->customer_name ?: 'Guest' }}</span></div>
                                    <div>Items: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->orderItems->sum('quantity') }}</span></div>
                                    <div class="text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">₹{{ number_format((float) $order->total_amount, 2) }}</div>
                                </div>

                                <!-- Card items list summary -->
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100/60 dark:border-gray-700/50 pt-2">
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach($order->orderItems->take(3) as $item)
                                            <li>{{ $item->quantity }}x {{ $item->item_name }}</li>
                                        @endforeach
                                        @if($order->orderItems->count() > 3)
                                            <li class="list-none italic text-gray-400">+ {{ $order->orderItems->count() - 3 }} more</li>
                                        @endif
                                    </ul>
                                </div>

                                <!-- Card Actions -->
                                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100/60 dark:border-gray-700/50">
                                    @if($statusKey === 'pending')
                                        <button wire:click="confirmOrder({{ $order->id }})" class="flex-1 text-[11px] font-bold bg-blue-600 hover:bg-blue-500 text-white py-1 px-2 rounded-lg transition text-center shadow-sm">
                                            Confirm
                                        </button>
                                        <button wire:click="cancelOrder({{ $order->id }})" class="text-[11px] font-bold bg-gray-100 hover:bg-red-50 hover:text-red-600 dark:bg-gray-700 dark:hover:bg-red-950/20 dark:text-gray-300 py-1 px-2 rounded-lg transition">
                                            Void
                                        </button>
                                    @elseif($statusKey === 'confirmed')
                                        <button wire:click="startPreparing({{ $order->id }})" class="flex-1 text-[11px] font-bold bg-amber-600 hover:bg-amber-500 text-white py-1 px-2 rounded-lg transition text-center shadow-sm">
                                            Prepare
                                        </button>
                                    @elseif($statusKey === 'preparing')
                                        <button wire:click="markReady({{ $order->id }})" class="flex-1 text-[11px] font-bold bg-purple-600 hover:bg-purple-500 text-white py-1 px-2 rounded-lg transition text-center shadow-sm">
                                            Done
                                        </button>
                                    @elseif($statusKey === 'ready')
                                        <button wire:click="serveOrder({{ $order->id }})" class="flex-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white py-1 px-2 rounded-lg transition text-center shadow-sm">
                                            Serve
                                        </button>
                                    @elseif($statusKey === 'served')
                                        <button wire:click="printReceipt({{ $order->id }})" class="flex-1 text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 py-1 px-2 rounded-lg transition text-center">
                                            Receipt
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-xs text-gray-400 dark:text-gray-500">
                                No orders.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-filament-panels::page>
