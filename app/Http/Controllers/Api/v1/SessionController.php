<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\SessionService;
use App\Models\CustomerSession;
use App\Events\WaiterCalled;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SessionController extends BaseApiController
{
    public function __construct(protected SessionService $sessionService)
    {
    }

    /**
     * Start a guest session on a Table/Room.
     */
    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_type' => 'required|in:table,room,parcel',
            'sessionable_id' => 'required|integer',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'pax_count' => 'nullable|integer|min:1',
            'shift_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            $session = $this->sessionService->startSession($request->all());

            return $this->successResponse([
                'session_token' => $session->session_token,
                'session' => $session,
            ], 'Session started successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Validate an existing session token.
     */
    public function validateSession(Request $request): JsonResponse
    {
        $token = $request->header('X-Session-Token') ?: $request->input('session_token');

        if (!$token) {
            return $this->errorResponse('Session token is required', 400);
        }

        $session = CustomerSession::where('session_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return $this->errorResponse('Session is invalid or closed', 404);
        }

        return $this->successResponse($session, 'Session is active');
    }

    /**
     * Join an existing active session.
     */
    public function join(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            $session = $this->sessionService->joinSession(
                $request->input('session_token'),
                $request->input('customer_name'),
                $request->input('customer_phone')
            );

            return $this->successResponse([
                'session_token' => $session->session_token,
                'session' => $session,
            ], 'Joined session successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Customer request for waiter assistance.
     */
    public function callWaiter(Request $request): JsonResponse
    {
        $token = $request->header('X-Session-Token') ?: $request->input('session_token');

        if (!$token) {
            return $this->errorResponse('Session token is required', 400);
        }

        $session = CustomerSession::where('session_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return $this->errorResponse('Session is invalid or closed', 404);
        }

        // Determine location name
        $locationName = 'Counter';
        $sessionable = $session->sessionable;
        if ($sessionable) {
            $locationName = $sessionable->name ?: $sessionable->room_number ?? 'Table';
        }

        // Dispatch alert event
        event(new WaiterCalled($session->session_token, $locationName));

        return $this->successResponse(null, 'Waiter called successfully');
    }

    /**
     * Customer request for the final bill.
     */
    public function requestBill(Request $request): JsonResponse
    {
        $token = $request->header('X-Session-Token') ?: $request->input('session_token');

        if (!$token) {
            return $this->errorResponse('Session token is required', 400);
        }

        $session = CustomerSession::where('session_token', $token)
            ->where('is_primary', true)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return $this->errorResponse('Active primary session not found', 404);
        }

        $session->update([
            'status' => 'bill_requested'
        ]);

        return $this->successResponse(null, 'Bill requested successfully. A waiter will bring the invoice shortly.');
    }
}
