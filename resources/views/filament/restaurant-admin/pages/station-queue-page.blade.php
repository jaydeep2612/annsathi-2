<x-filament-panels::page>
    <div x-data="{
        ticketCount: 0,
        playChime() {
            try {
                let ctx = new (window.AudioContext || window.webkitAudioContext)();
                this.beep(ctx, 880, 0.1, 0.0);
                this.beep(ctx, 1109, 0.1, 0.15);
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
            this.$watch('ticketCount', (newVal, oldVal) => {
                if (newVal > oldVal && oldVal > 0) {
                    this.playChime();
                }
            });
        }
    }" class="flex flex-col gap-6">

        @php
            $stations = $this->getStations();
            $tickets = $this->getActiveTickets();
        @endphp

        <span x-init="ticketCount = {{ $tickets->count() }}" class="hidden"></span>

        <!-- Station Selector Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-gray-900/40 p-5 rounded-xl border border-gray-100 dark:border-gray-800">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">Active Station:</label>
                <select wire:model.live="selectedStationId" class="w-full sm:w-64 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-850 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                    <option value="">-- Select Station --</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="text-xs font-semibold px-3 py-1 bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400 rounded-full">
                {{ $tickets->count() }} Active Items Here
            </div>
        </div>

        @if($selectedStationId)
            <!-- Tickets Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($tickets as $ticket)
                    @php
                        $tableName = 'Takeaway';
                        if ($ticket->order && $ticket->order->customerSession && $ticket->order->customerSession->sessionable) {
                            $tableName = $ticket->order->customerSession->sessionable->name;
                        }

                        $borderClass = $ticket->current_status === 'placed' 
                            ? 'border-rose-300 dark:border-rose-900/50 shadow-rose-50/50 ring-2 ring-rose-500/20' 
                            : 'border-amber-300 dark:border-amber-900/50';

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
                                {{ $ticket->kitchenStation->name }}
                            </span>
                        </div>

                        <!-- Items -->
                        <div class="p-4 flex-1 flex flex-col gap-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                Placed: {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                            </div>

                            <div class="space-y-3">
                                @foreach($ticket->itemStatuses as $itemStatus)
                                    <div class="flex justify-between items-start text-xs border-b border-gray-100/60 dark:border-gray-700/40 pb-2">
                                        <div class="flex-1 pr-2">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $itemStatus->orderItem->quantity }}x</span>
                                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $itemStatus->orderItem->item_name }}</span>
                                            </div>
                                            @if($itemStatus->orderItem->notes)
                                                <p class="text-[10px] text-rose-600 dark:text-rose-400 italic mt-0.5">* NOTE: {{ $itemStatus->orderItem->notes }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center">
                                            @if($itemStatus->status === 'queued')
                                                <button wire:click="startPreparingItem({{ $itemStatus->id }})" class="text-[10px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/20 dark:hover:bg-amber-950/40 dark:text-amber-400 py-1 px-2 rounded-lg transition border border-amber-200/50">
                                                    Start
                                                </button>
                                            @elseif($itemStatus->status === 'preparing')
                                                <button wire:click="completeItem({{ $itemStatus->id }})" class="text-[10px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white py-1 px-2 rounded-lg transition shadow-sm">
                                                    Done
                                                </button>
                                            @else
                                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
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
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400">No active items for this station</h3>
                    </div>
                @endforelse
            </div>
        @else
            <div class="text-center py-12 bg-white dark:bg-gray-800 border border-gray-150 dark:border-gray-800 rounded-xl">
                <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400">Please select a kitchen station from the dropdown.</h3>
            </div>
        @endif

    </div>
</x-filament-panels::page>
