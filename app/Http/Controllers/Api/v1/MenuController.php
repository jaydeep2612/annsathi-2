<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends BaseApiController
{
    /**
     * Get the complete active menu catalog grouped by categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->with(['menuItems' => function ($query) {
                $query->where('is_available', true)
                    ->with(['variantGroups' => function ($q) {
                        $q->with('variants');
                    }]);
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->successResponse($categories, 'Menu catalog retrieved successfully');
    }

    /**
     * Get active categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    /**
     * Check availability status of a specific menu item.
     */
    public function checkAvailability(int $itemId): JsonResponse
    {
        $menuItem = MenuItem::findOrFail($itemId);

        return $this->successResponse([
            'menu_item_id' => $menuItem->id,
            'is_available' => $menuItem->is_available,
        ], "Item is " . ($menuItem->is_available ? "available" : "unavailable"));
    }
}
