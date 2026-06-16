<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Reservation;
use App\Domains\Reservations\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservationController extends BaseApiController
{
    public function __construct(protected ReservationService $reservationService)
    {
    }

    /**
     * List reservations.
     */
    public function index(Request $request): JsonResponse
    {
        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        $date = $request->query('date', Carbon::today()->toDateString());

        $reservations = Reservation::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereDate('reservation_time', $date)
            ->orderBy('reservation_time', 'asc')
            ->get();

        return $this->successResponse($reservations, 'Reservations retrieved successfully');
    }

    /**
     * Create a new reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'restaurant_table_id' => 'required|integer',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'reservation_time' => 'required|date_format:Y-m-d H:i:s',
            'pax_count' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:15',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            $reservation = $this->reservationService->createReservation($request->all());

            return $this->successResponse($reservation, 'Reservation created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            // Retrieve first to verify tenant boundaries
            $restaurantId = app('tenant.restaurant_id');
            $reservation = Reservation::where('restaurant_id', $restaurantId)->findOrFail($id);

            $cancelled = $this->reservationService->cancelReservation($reservation->id);

            return $this->successResponse($cancelled, 'Reservation cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
