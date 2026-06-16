<?php

declare(strict_types=1);

namespace App\Domains\Reservations\Services;

use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Services\SessionService;
use App\Domains\Reservations\Exceptions\ReservationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ReservationService
{
    protected SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Create a new table reservation.
     */
    public function createReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            $tableId = (int) $data['restaurant_table_id'];
            $paxCount = (int) $data['pax_count'];
            $reservationTime = Carbon::parse($data['reservation_time']);
            $durationMinutes = (int) ($data['duration_minutes'] ?? 120);

            $table = RestaurantTable::findOrFail($tableId);

            // 1. Capacity Check
            if ($paxCount > $table->capacity) {
                throw ReservationException::capacityExceeded($paxCount, $table->capacity);
            }

            // 2. Overlap Check (database-agnostic implementation)
            $start = $reservationTime;
            $end = (clone $start)->addMinutes($durationMinutes);
            
            $dayStart = (clone $start)->subDay();
            $dayEnd = (clone $end)->addDay();

            $excludeId = isset($data['id']) ? (int) $data['id'] : null;

            $existingReservations = Reservation::where('restaurant_table_id', $tableId)
                ->whereIn('status', ['pending', 'confirmed', 'seated'])
                ->whereBetween('reservation_time', [$dayStart, $dayEnd])
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->get();

            foreach ($existingReservations as $exist) {
                $existStart = $exist->reservation_time;
                $existEnd = (clone $existStart)->addMinutes($exist->duration_minutes);

                if ($existStart < $end && $existEnd > $start) {
                    throw ReservationException::tableOverbooked($table->name, $start->format('Y-m-d H:i'));
                }
            }

            // 3. Create Reservation
            $reservation = Reservation::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'restaurant_table_id' => $tableId,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'reservation_time' => $reservationTime,
                'duration_minutes' => $durationMinutes,
                'pax_count' => $paxCount,
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // If table status needs updating
            if ($reservation->status === 'confirmed' && $table->status === 'available') {
                $table->update(['status' => 'reserved']);
            }

            return $reservation;
        });
    }

    /**
     * Update an existing table reservation.
     */
    public function updateReservation(int $id, array $data): Reservation
    {
        return DB::transaction(function () use ($id, $data) {
            $reservation = Reservation::findOrFail($id);

            $tableId = (int) ($data['restaurant_table_id'] ?? $reservation->restaurant_table_id);
            $paxCount = (int) ($data['pax_count'] ?? $reservation->pax_count);
            $reservationTime = isset($data['reservation_time']) ? Carbon::parse($data['reservation_time']) : $reservation->reservation_time;
            $durationMinutes = (int) ($data['duration_minutes'] ?? $reservation->duration_minutes);

            $table = RestaurantTable::findOrFail($tableId);

            // 1. Capacity Check
            if ($paxCount > $table->capacity) {
                throw ReservationException::capacityExceeded($paxCount, $table->capacity);
            }

            // 2. Overlap Check
            $start = $reservationTime;
            $end = (clone $start)->addMinutes($durationMinutes);
            
            $dayStart = (clone $start)->subDay();
            $dayEnd = (clone $end)->addDay();

            $existingReservations = Reservation::where('restaurant_table_id', $tableId)
                ->whereIn('status', ['pending', 'confirmed', 'seated'])
                ->whereBetween('reservation_time', [$dayStart, $dayEnd])
                ->where('id', '!=', $id)
                ->get();

            foreach ($existingReservations as $exist) {
                $existStart = $exist->reservation_time;
                $existEnd = (clone $existStart)->addMinutes($exist->duration_minutes);

                if ($existStart < $end && $existEnd > $start) {
                    throw ReservationException::tableOverbooked($table->name, $start->format('Y-m-d H:i'));
                }
            }

            // 3. Update Reservation
            $reservation->update([
                'restaurant_table_id' => $tableId,
                'customer_id' => $data['customer_id'] ?? $reservation->customer_id,
                'customer_name' => $data['customer_name'] ?? $reservation->customer_name,
                'customer_phone' => $data['customer_phone'] ?? $reservation->customer_phone,
                'reservation_time' => $reservationTime,
                'duration_minutes' => $durationMinutes,
                'pax_count' => $paxCount,
                'status' => $data['status'] ?? $reservation->status,
                'notes' => $data['notes'] ?? $reservation->notes,
            ]);

            // If table status needs updating
            if ($reservation->status === 'confirmed' && $table->status === 'available') {
                $table->update(['status' => 'reserved']);
            }

            return $reservation;
        });
    }

    /**
     * Confirm a reservation.
     */
    public function confirmReservation(int $id): Reservation
    {
        return DB::transaction(function () use ($id) {
            $reservation = Reservation::findOrFail($id);
            $reservation->update(['status' => 'confirmed']);

            $table = $reservation->restaurantTable;
            if ($table && $table->status === 'available') {
                $table->update(['status' => 'reserved']);
            }

            return $reservation;
        });
    }

    /**
     * Seat a reservation, starting an active customer session.
     */
    public function seatReservation(int $id, ?int $shiftId = null): Reservation
    {
        return DB::transaction(function () use ($id, $shiftId) {
            $reservation = Reservation::findOrFail($id);

            if ($reservation->status === 'seated') {
                return $reservation;
            }

            $table = $reservation->restaurantTable;

            // Update table to available temporarily to allow SessionService to seat
            if ($table->status !== 'available') {
                $table->update(['status' => 'available']);
            }

            // Start session using SessionService
            $session = $this->sessionService->startSession([
                'session_type' => 'table',
                'sessionable_id' => $table->id,
                'customer_name' => $reservation->customer_name,
                'customer_phone' => $reservation->customer_phone,
                'pax_count' => $reservation->pax_count,
                'shift_id' => $shiftId,
            ]);

            $reservation->update(['status' => 'seated']);

            return $reservation;
        });
    }

    /**
     * Cancel a reservation.
     */
    public function cancelReservation(int $id): Reservation
    {
        return DB::transaction(function () use ($id) {
            $reservation = Reservation::findOrFail($id);
            $reservation->update(['status' => 'cancelled']);

            $table = $reservation->restaurantTable;
            if ($table && $table->status === 'reserved') {
                // Check if there are other confirmed reservations for this table
                $hasOtherConfirmed = Reservation::where('restaurant_table_id', $table->id)
                    ->where('status', 'confirmed')
                    ->exists();

                if (!$hasOtherConfirmed) {
                    $table->update(['status' => 'available']);
                }
            }

            return $reservation;
        });
    }
}
