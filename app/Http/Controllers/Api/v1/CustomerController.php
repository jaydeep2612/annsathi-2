<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseApiController
{
    /**
     * Get list of customers.
     */
    public function index(Request $request): JsonResponse
    {
        $restaurantId = app('tenant.restaurant_id');

        $customers = Customer::where('restaurant_id', $restaurantId)
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate($request->query('per_page', 15));

        return $this->successResponse($customers, 'Customers retrieved successfully');
    }

    /**
     * Create a new customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'loyalty_points' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            $restaurantId = app('tenant.restaurant_id');

            // Enforce uniqueness of phone per restaurant
            $existing = Customer::where('restaurant_id', $restaurantId)
                ->where('phone', $request->input('phone'))
                ->first();

            if ($existing) {
                return $this->errorResponse('Customer with this phone number already exists', 409);
            }

            $customer = Customer::create([
                'restaurant_id' => $restaurantId,
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'loyalty_points' => $request->input('loyalty_points', 0),
            ]);

            return $this->successResponse($customer, 'Customer created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Show customer details.
     */
    public function show(int $id): JsonResponse
    {
        $restaurantId = app('tenant.restaurant_id');

        $customer = Customer::where('restaurant_id', $restaurantId)
            ->with(['loyaltyTransactions'])
            ->findOrFail($id);

        return $this->successResponse($customer, 'Customer details retrieved');
    }
}
