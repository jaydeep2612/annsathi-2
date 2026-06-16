<x-filament-panels::page>
    <div x-data="{
        showWalkInModal: false,
        selectedTableId: null,
        selectedTableName: '',
        customerName: '',
        customerPhone: '',
        openWalkInModal(id, name) {
            this.selectedTableId = id;
            this.selectedTableName = name;
            this.customerName = '';
            this.customerPhone = '';
            this.showWalkInModal = true;
        },
        submitWalkIn() {
            $wire.seatWalkIn(this.selectedTableId, this.customerName, this.customerPhone);
            this.showWalkInModal = false;
        }
    }">

        <!-- Seating Status Overview -->
        @php
            $tables = $this->getTables();
            $total = $tables->count();
            $available = $tables->where('status', 'available')->count();
            $occupied = $tables->where('status', 'occupied')->count();
            $reserved = $tables->where('status', 'reserved')->count();
            $cleaning = $tables->where('status', 'cleaning')->count();
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Total Tables</span>
                <h3 class="text-2xl font-bold mt-1 text-gray-800 dark:text-white">{{ $total }}</h3>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-950/20 p-4 rounded-xl shadow-sm border border-emerald-100 dark:border-emerald-900/30">
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wider">Available</span>
                <h3 class="text-2xl font-bold mt-1 text-emerald-800 dark:text-emerald-400">{{ $available }}</h3>
            </div>
            <div class="bg-rose-50 dark:bg-rose-950/20 p-4 rounded-xl shadow-sm border border-rose-100 dark:border-rose-900/30">
                <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold uppercase tracking-wider">Occupied</span>
                <h3 class="text-2xl font-bold mt-1 text-rose-800 dark:text-rose-400">{{ $occupied }}</h3>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/20 p-4 rounded-xl shadow-sm border border-amber-100 dark:border-amber-900/30">
                <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase tracking-wider">Reserved</span>
                <h3 class="text-2xl font-bold mt-1 text-amber-800 dark:text-amber-400">{{ $reserved }}</h3>
            </div>
            <div class="bg-blue-50 dark:bg-blue-950/20 p-4 rounded-xl shadow-sm border border-blue-100 dark:border-blue-900/30">
                <span class="text-xs text-blue-600 dark:text-blue-400 font-semibold uppercase tracking-wider">Cleaning</span>
                <h3 class="text-2xl font-bold mt-1 text-blue-800 dark:text-blue-400">{{ $cleaning }}</h3>
            </div>
        </div>

        <!-- Seating Grid layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($tables as $table)
                @php
                    $session = $this->getActiveSession($table);
                    $reservation = $this->getTableReservation($table);
                    
                    // Card background dynamic classes
                    $cardClass = match($table->status) {
                        'available' => 'border-emerald-200 bg-emerald-50/30 dark:bg-emerald-950/5 hover:border-emerald-400',
                        'occupied' => 'border-rose-200 bg-rose-50/30 dark:bg-rose-950/5 hover:border-rose-400',
                        'reserved' => 'border-amber-200 bg-amber-50/30 dark:bg-amber-950/5 hover:border-amber-400',
                        'cleaning' => 'border-blue-200 bg-blue-50/30 dark:bg-blue-950/5 hover:border-blue-400',
                        default => 'border-gray-200 bg-gray-50/30'
                    };

                    $badgeClass = match($table->status) {
                        'available' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'occupied' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                        'reserved' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        'cleaning' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        default => 'bg-gray-100 text-gray-800'
                    };
                @endphp

                <div class="border rounded-xl shadow-sm p-5 flex flex-col justify-between transition-all duration-200 hover:shadow-md {{ $cardClass }}">
                    <div>
                        <!-- Header: Name & Status Badge -->
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $table->name }}</h4>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full uppercase {{ $badgeClass }}">
                                {{ $table->status }}
                            </span>
                        </div>

                        <!-- Info: Capacity -->
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 flex items-center">
                            <span class="mr-1">Capacity:</span>
                            <span class="font-semibold">{{ $table->capacity }} Pax</span>
                        </div>

                        <!-- Session Info / Reservation Info -->
                        @if($table->status === 'occupied' && $session)
                            <div class="mt-2 p-2.5 bg-rose-50/50 dark:bg-rose-950/10 rounded-lg text-xs border border-rose-100/30 dark:border-rose-900/10">
                                <p class="text-rose-700 dark:text-rose-400 font-semibold mb-0.5">Active Session</p>
                                <p class="text-gray-700 dark:text-gray-300 font-medium">Guest: {{ $session->customer_name }}</p>
                                @if($session->customer_phone)
                                    <p class="text-gray-500 dark:text-gray-400">Phone: {{ $session->customer_phone }}</p>
                                @endif
                                <p class="text-gray-500 dark:text-gray-400 mt-1">Token: {{ $session->session_token }}</p>
                            </div>
                        @elseif($table->status === 'reserved' && $reservation)
                            <div class="mt-2 p-2.5 bg-amber-50/50 dark:bg-amber-950/10 rounded-lg text-xs border border-amber-100/30 dark:border-amber-900/10">
                                <p class="text-amber-700 dark:text-amber-400 font-semibold mb-0.5">Booking Today</p>
                                <p class="text-gray-700 dark:text-gray-300 font-medium">Guest: {{ $reservation->customer_name }}</p>
                                <p class="text-gray-500 dark:text-gray-400">Time: {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }} ({{ $reservation->pax_count }} Pax)</p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="mt-5 border-t border-gray-100 dark:border-gray-700/50 pt-3 flex flex-wrap gap-2">
                        @if($table->status === 'available')
                            <button x-on:click="openWalkInModal({{ $table->id }}, '{{ $table->name }}')" class="flex-1 text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white py-1.5 px-3 rounded-lg shadow-sm transition">
                                Seat Walk-in
                            </button>
                            <button wire:click="markCleaning({{ $table->id }})" class="text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 py-1.5 px-3 rounded-lg transition">
                                Clean
                            </button>
                        @elseif($table->status === 'reserved' && $reservation)
                            <button wire:click="seatReservation({{ $reservation->id }})" class="flex-1 text-xs font-semibold bg-amber-600 hover:bg-amber-500 text-white py-1.5 px-3 rounded-lg shadow-sm transition">
                                Seat Booking
                            </button>
                            <button wire:click="markAvailable({{ $table->id }})" class="text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 py-1.5 px-3 rounded-lg transition">
                                Release
                            </button>
                        @elseif($table->status === 'cleaning')
                            <button wire:click="markAvailable({{ $table->id }})" class="flex-1 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white py-1.5 px-3 rounded-lg shadow-sm transition">
                                Mark Available
                            </button>
                        @elseif($table->status === 'occupied')
                            <span class="text-xs text-gray-500 dark:text-gray-400 italic py-1.5">Table in use. Use billing to close session.</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Walk-in Seating Modal (Alpine.js) -->
        <div x-show="showWalkInModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none" x-cloak>
            <!-- Backdrop -->
            <div x-show="showWalkInModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/55 backdrop-blur-sm transition-opacity" x-on:click="showWalkInModal = false"></div>

            <!-- Modal Content -->
            <div x-show="showWalkInModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md mx-4 p-6 z-10">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Seat Guest — <span x-text="selectedTableName"></span></h3>
                    <button x-on:click="showWalkInModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form x-on:submit.prevent="submitWalkIn()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Guest Name</label>
                            <input type="text" x-model="customerName" placeholder="e.g. Guest" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone Number (Optional)</label>
                            <input type="text" x-model="customerPhone" placeholder="e.g. 9999999999" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" x-on:click="showWalkInModal = false" class="px-4 py-2 text-sm font-semibold bg-gray-100 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition shadow-sm">
                            Confirm & Seat
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-filament-panels::page>
