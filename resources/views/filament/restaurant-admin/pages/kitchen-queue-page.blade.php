<x-filament-panels::page>
    <div x-data="{
        ticketCount: 0,
        playChime() {
            try {
                let ctx = new (window.AudioContext || window.webkitAudioContext)();
                // Play a pleasant double-beep chime
                this.beep(ctx, 880, 0.1, 0.0);
                this.beep(ctx, 1109, 0.1, 0.15); // C#6
            } catch (e) {
                console.error('Audio synthesis blocked or not supported');
            }
        },
        beep(ctx, freq, duration, delay) {
            let osc = ctx.createOscillator();
            let gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, ctx.currentTime + delay);
            osc.connect(gain);
            gain.connect(ctx.destination);
            gain.gain.setValueAtTime(0.08, ctx.currentTime + delay);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + duration);
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + duration);
        },
        init() {
            // Watch for changes in ticket count
            this.$watch('ticketCount', (newVal, oldVal) => {
                if (newVal > oldVal && oldVal > 0) {
                    this.playChime();
                }
            });
        }
    }" class="flex flex-col gap-6">

        @php
            $tickets = $this->getActiveTickets();
        @endphp

        <!-- Sync ticket count to Alpine.js -->
        <span x-init="ticketCount = {{ $tickets->count() }}" class="hidden"></span>

        <!-- Kitchen displays count overview -->
        <div class="flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/40 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Active Kitchen Tickets</h3>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                    {{ $tickets->count() }} Live Tickets
                </span>
            </div>
            <button x-on:click="playChime()" class="text-xs font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M12 18.75V5.25L7.75 9.5H4.5v5h3.25L12 18.75z" />
                </svg>
                Test Sound Alert
            </button>
        </div>

        <!-- Tickets Cards Display Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($tickets as $ticket)
                @php
                    $tableName = 'Takeaway';
                    if ($ticket->order && $ticket->order->customerSession && $ticket->order->customerSession->sessionable) {
                        $tableName = $ticket->order->customerSession->sessionable->name;
                    }

                    // Ticket header and border color
                    $borderClass = $ticket->current_status === 'placed' 
                        ? 'border-rose-300 dark:border-rose-900/50 shadow-rose-50/50 ring-2 ring-rose-500/20' 
                        : 'border-amber-300 dark:border-amber-900/50 shadow-amber-50/50';

                    $headerBg = $ticket->current_status === 'placed' 
                        ? 'bg-rose-50/80 dark:bg-rose-950/20 text-rose-800 dark:text-rose-400' 
                        : 'bg-amber-50/80 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400';
                @endphp

                <div class="border rounded-xl shadow-sm bg-white dark:bg-gray-800 flex flex-col justify-between overflow-hidden {{ $borderClass }}">
                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center {{ $headerBg }}">
                        <div>
                            <span class="text-[10px] font-bold tracking-wider uppercase opacity-80">Order #{{ $ticket->order_id }}</span>
                            <h4 class="text-sm font-bold mt-0.5">{{ $tableName }}</h4>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-white dark:bg-gray-850 shadow-sm">
                            {{ $ticket->kitchenStation ? $ticket->kitchenStation->name : 'Kitchen' }}
                        </span>
                    </div>

                    <!-- Items List -->
                    <div class="p-4 flex-1 flex flex-col gap-3">
                        <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Placed: {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}</span>
                            @if($ticket->assignedChef)
                                <span>Chef: {{ $ticket->assignedChef->name }}</span>
                            @endif
                        </div>

                        <div class="space-y-3">
                            @foreach($ticket->itemStatuses as $itemStatus)
                                <div class="flex justify-between items-start text-xs border-b border-gray-100/60 dark:border-gray-700/40 pb-2">
                                    <div class="flex-1 pr-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $itemStatus->orderItem->quantity }}x</span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $itemStatus->orderItem->item_name }}</span>
                                            @if($itemStatus->orderItem->item_variant_label)
                                                <span class="text-[10px] font-medium px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ $itemStatus->orderItem->item_variant_label }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($itemStatus->orderItem->notes)
                                            <p class="text-[10px] text-rose-600 dark:text-rose-400 italic mt-0.5">* NOTE: {{ $itemStatus->orderItem->notes }}</p>
                                        @endif
                                    </div>

                                    <!-- Item Action / Status -->
                                    <div class="flex items-center">
                                        @if($itemStatus->status === 'queued')
                                            <button wire:click="startPreparingItem({{ $itemStatus->id }})" class="text-[10px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/20 dark:hover:bg-amber-950/40 dark:text-amber-400 py-1 px-2 rounded-lg transition border border-amber-200/50 dark:border-amber-900/30">
                                                Start
                                            </button>
                                        @elseif($itemStatus->status === 'preparing')
                                            <button wire:click="completeItem({{ $itemStatus->id }})" class="text-[10px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white py-1 px-2 rounded-lg transition shadow-sm">
                                                Done
                                            </button>
                                        @else
                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Ready
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer Action -->
                    @if($ticket->current_status === 'placed')
                        <div class="p-3 bg-gray-50/60 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700/50">
                            <button wire:click="startPreparingTicket({{ $ticket->id }})" class="w-full text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white py-2 px-3 rounded-lg transition text-center shadow-sm">
                                Start Ticket
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 border border-gray-150 dark:border-gray-800 rounded-xl">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-bold text-gray-500 dark:text-gray-400">No active kitchen orders</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">KDS tickets will appear here when POS orders are confirmed.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-filament-panels::page>
