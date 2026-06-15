<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\CustomerSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseApiController
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * Place a new order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_type' => 'required|in:dine_in,room_service,parcel,manual',
            'customer_session_id' => 'nullable|integer',
            'assigned_waiter_id' => 'nullable|integer',
            'discount_type' => 'nullable|in:flat,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'shift_id' => 'nullable|integer',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|integer',
            'items.*.selected_variant_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            // If customer placing order, verify session token
            $sessionToken = $request->header('X-Session-Token') ?: $request->input('session_token');
            $sessionData = $request->all();

            if ($sessionToken) {
                $session = CustomerSession::where('session_token', $sessionToken)
                    ->where('status', 'active')
                    ->first();

                if (!$session) {
                    return $this->errorResponse('Session is invalid or closed', 403);
                }

                $sessionData['customer_session_id'] = $session->id;
                $sessionData['service_type'] = $session->session_type === 'table' ? 'dine_in' : ($session->session_type === 'room' ? 'room_service' : 'parcel');
            }

            $order = $this->orderService->createOrder($sessionData);

            return $this->successResponse($order->load('items'), 'Order placed successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * View a specific order.
     */
    public function show(int $orderId): JsonResponse
    {
        $order = Order::with(['items', 'statusLogs'])->findOrFail($orderId);

        // Security check: if customer, verify session association
        $sessionToken = request()->header('X-Session-Token') ?: request()->input('session_token');
        if ($sessionToken) {
            $session = CustomerSession::where('session_token', $sessionToken)->first();
            if (!$session || $order->customer_session_id !== $session->id) {
                return $this->errorResponse('Unauthorized access to order details', 403);
            }
        }

        return $this->successResponse($order, 'Order details retrieved');
    }

    /**
     * Cancel a pending/confirmed order.
     */
    public function cancel(Request $request, int $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            $order = Order::findOrFail($orderId);

            // Security check: if customer, verify session association
            $sessionToken = $request->header('X-Session-Token') ?: $request->input('session_token');
            if ($sessionToken) {
                $session = CustomerSession::where('session_token', $sessionToken)->first();
                if (!$session || $order->customer_session_id !== $session->id) {
                    return $this->errorResponse('Unauthorized access to cancel order', 403);
                }
            }

            $cancelledOrder = $this->orderService->cancelOrder($orderId, $request->input('reason'));

            return $this->successResponse($cancelledOrder, 'Order cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
