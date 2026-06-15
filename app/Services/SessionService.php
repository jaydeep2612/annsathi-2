<?php

namespace App\Services;

use App\Models\CustomerSession;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ParcelCounter;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SessionService
{
    /**
     * Start a new customer session on a table, room, or parcel counter.
     */
    public function startSession(array $data): CustomerSession
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');
            $sessionType = $data['session_type']; // table, room, parcel
            $sessionableId = $data['sessionable_id'];

            // 1. Resolve polymorphic sessionable details and validate availability
            $sessionableType = null;
            if ($sessionType === 'table') {
                $sessionableType = RestaurantTable::class;
                $table = RestaurantTable::lockForUpdate()->findOrFail($sessionableId);
                if ($table->status !== 'available') {
                    throw new Exception("Table {$table->name} is currently occupied or unavailable.");
                }
                $table->update(['status' => 'occupied']);
            } elseif ($sessionType === 'room') {
                $sessionableType = Room::class;
                $room = Room::lockForUpdate()->findOrFail($sessionableId);
                if ($room->status !== 'available') {
                    throw new Exception("Room {$room->room_number} is currently occupied or unavailable.");
                }
                $room->update(['status' => 'occupied']);
            } elseif ($sessionType === 'parcel') {
                $sessionableType = ParcelCounter::class;
                // Takeaway counters don't block multiple orders/sessions
            }

            // 2. Generate unique session token
            $sessionToken = 'SES-' . Str::upper(Str::random(12));

            // 3. Create Session
            $session = CustomerSession::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'session_type' => $sessionType,
                'session_token' => $sessionToken,
                'sessionable_type' => $sessionableType,
                'sessionable_id' => $sessionableId,
                'customer_name' => $data['customer_name'] ?? 'Guest',
                'customer_phone' => $data['customer_phone'] ?? null,
                'pax_count' => $data['pax_count'] ?? 1,
                'status' => 'active',
                'is_primary' => true,
                'check_in_at' => now(),
                'expires_at' => now()->addHours(4), // default 4 hour session
                'shift_id' => $data['shift_id'] ?? null,
            ]);

            return $session;
        });
    }

    /**
     * Let a customer join an existing primary session (e.g. sharing a table).
     */
    public function joinSession(string $token, string $customerName, ?string $customerPhone = null): CustomerSession
    {
        return DB::transaction(function () use ($token, $customerName, $customerPhone) {
            $primarySession = CustomerSession::where('session_token', $token)
                ->where('is_primary', true)
                ->where('status', 'active')
                ->firstOrFail();

            // Create participant session
            $session = CustomerSession::create([
                'restaurant_id' => $primarySession->restaurant_id,
                'branch_id' => $primarySession->branch_id,
                'session_type' => $primarySession->session_type,
                'session_token' => 'SES-JOIN-' . Str::upper(Str::random(8)),
                'sessionable_type' => $primarySession->sessionable_type,
                'sessionable_id' => $primarySession->sessionable_id,
                'host_session_id' => $primarySession->id,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'pax_count' => 1,
                'status' => 'active',
                'is_primary' => false,
                'check_in_at' => now(),
                'expires_at' => $primarySession->expires_at,
                'shift_id' => $primarySession->shift_id,
            ]);

            return $session;
        });
    }

    /**
     * Close a customer session. Blocks if there are unpaid orders.
     */
    public function closeSession(int $sessionId, ?int $closedBy = null): CustomerSession
    {
        return DB::transaction(function () use ($sessionId, $closedBy) {
            $session = CustomerSession::lockForUpdate()->findOrFail($sessionId);

            if ($session->status === 'closed') {
                return $session;
            }

            // 1. Verify all session orders are settled/paid/cancelled
            $unpaidCount = Order::where('customer_session_id', $session->id)
                ->whereNotIn('payment_status', ['paid', 'waived', 'refunded'])
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($unpaidCount > 0) {
                throw new Exception("Cannot close session. There are {$unpaidCount} unpaid orders associated with this session.");
            }

            // 2. Transition Session
            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $closedBy ?: auth()->id(),
            ]);

            // 3. Revert table/room status if this is the primary session
            if ($session->is_primary) {
                if ($session->sessionable_type === RestaurantTable::class) {
                    $table = RestaurantTable::find($session->sessionable_id);
                    if ($table) {
                        $table->update(['status' => 'available']);
                    }
                } elseif ($session->sessionable_type === Room::class) {
                    $room = Room::find($session->sessionable_id);
                    if ($room) {
                        $room->update(['status' => 'available']);
                    }
                }

                // Close all secondary session participants as well
                CustomerSession::where('host_session_id', $session->id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'closed',
                        'closed_at' => now(),
                        'closed_by' => $closedBy ?: auth()->id(),
                    ]);
            }

            return $session;
        });
    }
}
