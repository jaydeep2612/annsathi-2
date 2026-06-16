<?php

namespace App\Policies;

use App\Models\User;

class GenericPolicy
{
    /**
     * Map model classes to their respective Spatie permissions.
     */
    protected function getPermissionFor($model): string
    {
        $class = is_object($model) ? get_class($model) : (string) $model;

        return match ($class) {
            \App\Models\Branch::class => 'manage_branches',
            \App\Models\User::class, \Spatie\Permission\Models\Role::class => 'manage_users',
            \App\Models\MenuItem::class,
            \App\Models\Category::class,
            \App\Models\ItemVariantGroup::class,
            \App\Models\ItemVariant::class,
            \App\Models\Recipe::class,
            \App\Models\RecipeVersion::class => 'manage_menu',
            \App\Models\GroceryItem::class,
            \App\Models\Warehouse::class,
            \App\Models\PurchaseOrder::class,
            \App\Models\GoodsReceipt::class => 'manage_inventory',
            \App\Models\Supplier::class,
            \App\Models\SupplierLedger::class => 'manage_suppliers',
            \App\Models\Order::class => 'place_manual_orders',
            \App\Models\Reservation::class => 'place_manual_orders',
            \App\Models\Refund::class,
            \App\Models\ApprovalRequest::class => 'approve_refunds',
            \App\Models\Shift::class,
            \App\Models\CashDrawer::class,
            \App\Models\CashMovement::class => 'manage_shifts',
            default => 'manage_settings',
        };
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, ?string $modelClass = null): bool
    {
        return $user->is_super_admin || $user->hasPermissionTo($this->getPermissionFor($modelClass));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model): bool
    {
        return $user->is_super_admin || $user->hasPermissionTo($this->getPermissionFor($model));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ?string $modelClass = null): bool
    {
        return $user->is_super_admin || $user->hasPermissionTo($this->getPermissionFor($modelClass));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model): bool
    {
        return $user->is_super_admin || $user->hasPermissionTo($this->getPermissionFor($model));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model): bool
    {
        return $user->is_super_admin || $user->hasPermissionTo($this->getPermissionFor($model));
    }
}
