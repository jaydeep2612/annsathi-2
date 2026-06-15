<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $requested_by
 * @property int|null $approved_by
 * @property string $entity_type
 * @property int|null $entity_id
 * @property string $action
 * @property string|null $reason
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $entity
 * @property-read \App\Models\User $requester
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperApprovalRequest {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $restaurant_id
 * @property int|null $user_id
 * @property string $event
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Restaurant|null $restaurant
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAuditLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $customer_session_id
 * @property string|null $name
 * @property numeric|null $target_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CustomerSession $customerSession
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BillGroupItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereCustomerSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBillGroup {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $bill_group_id
 * @property int $order_item_id
 * @property numeric $quantity
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BillGroup $billGroup
 * @property-read \App\Models\OrderItem $orderItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereBillGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillGroupItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBillGroupItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone_no
 * @property string|null $upi_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch wherePhoneNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Branch whereUpiId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBranch {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $branch_id
 * @property int $menu_item_id
 * @property bool $is_available
 * @property numeric|null $override_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch $branch
 * @property-read \App\Models\MenuItem|null $menuItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereOverridePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchMenuItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBranchMenuItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $shift_id
 * @property int $opened_by
 * @property int|null $closed_by
 * @property numeric $opening_balance
 * @property numeric|null $closing_balance
 * @property numeric|null $expected_closing_balance
 * @property numeric|null $variance
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $opened_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\User|null $closer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashMovement> $movements
 * @property-read int|null $movements_count
 * @property-read \App\Models\User $opener
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereClosedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereClosingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereExpectedClosingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereOpenedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashDrawer whereVariance($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCashDrawer {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $cash_drawer_id
 * @property int $restaurant_id
 * @property string $type
 * @property numeric $amount
 * @property string $reason
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property int $recorded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\CashDrawer $cashDrawer
 * @property-read \App\Models\User $operator
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $reference
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereCashDrawerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashMovement whereType($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCashMovement {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property string $name
 * @property string $slug
 * @property string|null $image_path
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItem> $menuItems
 * @property-read int|null $menu_items_count
 * @property-read Category|null $parent
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCategory {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int|null $invoice_id
 * @property int|null $order_id
 * @property string $credit_note_number
 * @property string $reason
 * @property numeric $amount
 * @property int $issued_by
 * @property int|null $approved_by
 * @property array<array-key, mixed>|null $items_snapshot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\User $issuer
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereCreditNoteNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereItemsSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditNote whereRestaurantId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCreditNote {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $session_type
 * @property string $session_token
 * @property string $sessionable_type
 * @property int $sessionable_id
 * @property int|null $host_session_id
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property int $pax_count
 * @property string $status
 * @property bool $is_primary
 * @property string|null $join_status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $check_in_at
 * @property \Illuminate\Support\Carbon|null $check_out_at
 * @property \Illuminate\Support\Carbon|null $actual_checkout_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int|null $closed_by
 * @property int|null $shift_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\User|null $closer
 * @property-read CustomerSession|null $host
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CustomerSession> $participants
 * @property-read int|null $participants_count
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $sessionable
 * @property-read \App\Models\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereActualCheckoutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereCheckInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereCheckOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereClosedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereHostSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereJoinStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession wherePaxCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereSessionToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereSessionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereSessionableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereSessionableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSession whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCustomerSession {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon $summary_date
 * @property int $grocery_item_id
 * @property numeric $opening_stock
 * @property numeric $additions
 * @property numeric $consumed
 * @property numeric $waste
 * @property numeric $adjustments
 * @property numeric $closing_stock
 * @property numeric $waste_cost
 * @property numeric $consumption_cost
 * @property \Illuminate\Support\Carbon|null $computed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereAdditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereAdjustments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereClosingStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereComputedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereConsumed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereConsumptionCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereOpeningStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereSummaryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereWaste($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyInventorySummary whereWasteCost($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDailyInventorySummary {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon $summary_date
 * @property int|null $shift_id
 * @property int $total_orders
 * @property int $completed_orders
 * @property int $cancelled_orders
 * @property numeric $gross_revenue
 * @property numeric $discount_total
 * @property numeric $tax_total
 * @property numeric $extra_charges_total
 * @property numeric $net_revenue
 * @property numeric $cash_collected
 * @property numeric $upi_collected
 * @property numeric $card_collected
 * @property numeric $complimentary_total
 * @property numeric $avg_order_value
 * @property int|null $peak_hour
 * @property int $dine_in_orders
 * @property int $room_service_orders
 * @property int $parcel_orders
 * @property int $manual_orders
 * @property \Illuminate\Support\Carbon|null $computed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereAvgOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereCancelledOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereCardCollected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereCashCollected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereCompletedOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereComplimentaryTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereComputedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereDineInOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereDiscountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereExtraChargesTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereGrossRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereManualOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereNetRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereParcelOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary wherePeakHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereRoomServiceOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereSummaryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereTaxTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereTotalOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesSummary whereUpiCollected($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDailySalesSummary {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $received_by
 * @property \Illuminate\Support\Carbon $receipt_date
 * @property int|null $approval_request_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ApprovalRequest|null $approvalRequest
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GoodsReceiptItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PurchaseOrder|null $purchaseOrder
 * @property-read \App\Models\User $receiver
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereApprovalRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereReceiptDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceipt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGoodsReceipt {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $goods_receipt_id
 * @property int $purchase_order_item_id
 * @property int $grocery_item_id
 * @property numeric $quantity_received
 * @property numeric $unit_cost
 * @property numeric $total_cost
 * @property string|null $batch_number
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property string $quality_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GoodsReceipt $goodsReceipt
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\PurchaseOrderItem $purchaseOrderItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereBatchNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereGoodsReceiptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem wherePurchaseOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereQualityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereQuantityReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoodsReceiptItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGoodsReceiptItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $measurement_unit_id
 * @property int|null $supplier_id
 * @property string $name
 * @property string|null $sku
 * @property numeric $current_stock
 * @property numeric $low_stock_threshold
 * @property numeric|null $reorder_quantity
 * @property numeric|null $cost_per_unit
 * @property numeric|null $avg_cost_per_unit
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventoryBatch> $batches
 * @property-read int|null $batches_count
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\MeasurementUnit|null $measurementUnit
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereAvgCostPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereCostPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereCurrentStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereLowStockThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereMeasurementUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereReorderQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroceryItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGroceryItem {}
}

namespace App\Models{
/**
 * @property string $key
 * @property int|null $restaurant_id
 * @property string $scope
 * @property string $status
 * @property int|null $reference_id
 * @property array<array-key, mixed>|null $response
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Restaurant|null $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IdempotencyKey whereStatus($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperIdempotencyKey {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $grocery_item_id
 * @property string|null $batch_number
 * @property int|null $supplier_id
 * @property numeric $initial_quantity
 * @property numeric $current_quantity
 * @property numeric $unit_cost
 * @property \Illuminate\Support\Carbon $received_date
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereBatchNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereCurrentQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereInitialQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereReceivedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryBatch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperInventoryBatch {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $grocery_item_id
 * @property int|null $inventory_batch_id
 * @property string $type
 * @property numeric $quantity
 * @property numeric $balance_after
 * @property numeric|null $unit_cost
 * @property numeric|null $total_cost
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $notes
 * @property int|null $performed_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InventoryBatch|null $batch
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\User|null $operator
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $reference
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereInventoryBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction wherePerformedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryTransaction withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperInventoryTransaction {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int|null $order_id
 * @property int|null $payment_id
 * @property int|null $customer_session_id
 * @property int|null $shift_id
 * @property string $invoice_number
 * @property string $invoice_prefix
 * @property int $invoice_sequence
 * @property \Illuminate\Support\Carbon $invoice_date
 * @property string|null $gstin
 * @property string|null $place_of_supply
 * @property string|null $customer_name
 * @property numeric $subtotal
 * @property numeric $discount_amount
 * @property numeric $tax_rate
 * @property numeric $tax_amount
 * @property numeric $extra_charges
 * @property string|null $extra_charges_label
 * @property numeric $grand_total
 * @property array<array-key, mixed> $items_snapshot
 * @property int|null $voided_by_credit_note_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\CreditNote|null $creditNote
 * @property-read \App\Models\CustomerSession|null $customerSession
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereExtraCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereExtraChargesLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereGstin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoicePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereItemsSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePlaceOfSupply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereVoidedByCreditNoteId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperInvoice {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon $summary_date
 * @property int $menu_item_id
 * @property int|null $item_variant_id
 * @property int $quantity_sold
 * @property numeric $gross_revenue
 * @property numeric $food_cost
 * @property numeric $gross_profit
 * @property numeric|null $food_cost_pct
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\MenuItem|null $menuItem
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\ItemVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereFoodCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereFoodCostPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereGrossProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereGrossRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereItemVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereQuantitySold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereSummaryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemSalesSummary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperItemSalesSummary {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $variant_group_id
 * @property int $menu_item_id
 * @property string $label
 * @property numeric $price_modifier
 * @property string $price_type
 * @property numeric|null $quantity_value
 * @property string|null $quantity_unit
 * @property bool $affects_inventory
 * @property int $sort_order
 * @property bool $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MenuItem|null $menuItem
 * @property-read \App\Models\ItemVariantGroup $variantGroup
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereAffectsInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant wherePriceModifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereQuantityUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereQuantityValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariant whereVariantGroupId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperItemVariant {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $menu_item_id
 * @property string $name
 * @property bool $is_required
 * @property int $min_select
 * @property int $max_select
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MenuItem|null $menuItem
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItemVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereMaxSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereMinSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemVariantGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperItemVariantGroup {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int|null $kitchen_station_id
 * @property int|null $branch_id
 * @property string $priority
 * @property string $current_status
 * @property int|null $assigned_chef_id
 * @property \Illuminate\Support\Carbon|null $acknowledged_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\User|null $chef
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItemKitchenStatus> $itemStatuses
 * @property-read int|null $item_statuses_count
 * @property-read \App\Models\KitchenStation|null $kitchenStation
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereAcknowledgedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereAssignedChefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereCurrentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereKitchenStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenQueue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperKitchenQueue {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property string|null $display_color
 * @property string|null $printer_ip
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItem> $menuItems
 * @property-read int|null $menu_items_count
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereDisplayColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation wherePrinterIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenStation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperKitchenStation {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property string $name
 * @property string|null $description
 * @property numeric $base_price
 * @property string $type
 * @property string $item_nature
 * @property array<array-key, mixed>|null $allergens
 * @property int|null $prep_time_minutes
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereAllergens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereItemNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem wherePrepTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterMenuItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMasterMenuItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property string $name
 * @property string $short_name
 * @property string|null $base_unit
 * @property numeric $conversion_factor
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereBaseUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereConversionFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeasurementUnit withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMeasurementUnit {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $category_id
 * @property int|null $master_menu_item_id
 * @property int|null $kitchen_station_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property numeric $base_price
 * @property string|null $image_path
 * @property string $type
 * @property string $item_nature
 * @property bool $is_available
 * @property bool $is_featured
 * @property int|null $prep_time_minutes
 * @property array<array-key, mixed>|null $allergens
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BranchMenuItem> $branchOverrides
 * @property-read int|null $branch_overrides_count
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\KitchenStation|null $kitchenStation
 * @property-read \App\Models\MasterMenuItem|null $masterMenuItem
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItemVariantGroup> $variantGroups
 * @property-read int|null $variant_groups_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereAllergens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereItemNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereKitchenStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereMasterMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem wherePrepTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMenuItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $driver
 * @property array<array-key, mixed>|null $settings
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationChannel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperNotificationChannel {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $user_id
 * @property string $event_name
 * @property array<array-key, mixed> $channels
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereChannels($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperNotificationPreference {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $restaurant_id
 * @property string $event_name
 * @property string $title
 * @property string $body
 * @property array<array-key, mixed> $channels
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Restaurant|null $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereChannels($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperNotificationTemplate {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int|null $user_id
 * @property string $type
 * @property string $title
 * @property string|null $body
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationsLog whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperNotificationsLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int|null $customer_session_id
 * @property string $service_type
 * @property string $status
 * @property string $payment_status
 * @property int|null $parent_order_id
 * @property bool $is_merged
 * @property int|null $bill_group_id
 * @property int|null $assigned_waiter_id
 * @property int|null $created_by
 * @property string|null $customer_name
 * @property string|null $notes
 * @property numeric $subtotal
 * @property string|null $discount_type
 * @property numeric $discount_value
 * @property numeric $discount_amount
 * @property numeric $tax_rate
 * @property numeric $tax_amount
 * @property numeric $extra_charges
 * @property string|null $extra_charges_label
 * @property numeric $total_amount
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $prepared_at
 * @property \Illuminate\Support\Carbon|null $served_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int|null $shift_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BillGroup|null $billGroup
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $childOrders
 * @property-read int|null $child_orders_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\CustomerSession|null $customerSession
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read Order|null $parentOrder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Shift|null $shift
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderStatusLog> $statusLogs
 * @property-read int|null $status_logs_count
 * @property-read \App\Models\User|null $waiter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAssignedWaiterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereExtraCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereExtraChargesLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIsMerged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereParentOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePreparedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereServedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrder {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int|null $menu_item_id
 * @property string $item_name
 * @property string|null $item_variant_label
 * @property int|null $selected_variant_id
 * @property numeric $unit_price
 * @property int $quantity
 * @property numeric $total_price
 * @property string $item_nature
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderItemKitchenStatus|null $kitchenStatus
 * @property-read \App\Models\MenuItem|null $menuItem
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\ItemVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereItemNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereItemVariantLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSelectedVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrderItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_item_id
 * @property int $kitchen_station_id
 * @property int $kitchen_queue_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KitchenQueue $kitchenQueue
 * @property-read \App\Models\KitchenStation $kitchenStation
 * @property-read \App\Models\OrderItem $orderItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereKitchenQueueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereKitchenStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemKitchenStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrderItemKitchenStatus {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $parent_order_id
 * @property int $merged_order_id
 * @property int $merged_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Order $mergedOrder
 * @property-read \App\Models\User $operator
 * @property-read \App\Models\Order $parentOrder
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog whereMergedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog whereMergedOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog whereParentOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderMergeLog whereRestaurantId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrderMergeLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int|null $changed_by
 * @property string $from_status
 * @property string $to_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStatusLog whereToStatus($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrderStatusLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property string $qr_token
 * @property string|null $qr_image_path
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomerSession> $sessions
 * @property-read int|null $sessions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereQrImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereQrToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcelCounter whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperParcelCounter {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $order_id
 * @property int|null $bill_group_id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int|null $shift_id
 * @property string $payment_method
 * @property numeric $amount
 * @property string|null $reference_note
 * @property int $received_by
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BillGroup|null $billGroup
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User $receiver
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereBillGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReferenceNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPayment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $supplier_id
 * @property string $po_number
 * @property string $status
 * @property int $ordered_by
 * @property \Illuminate\Support\Carbon|null $expected_delivery_date
 * @property string|null $notes
 * @property numeric $total_amount
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GoodsReceipt> $goodsReceipts
 * @property-read int|null $goods_receipts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User $orderer
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereExpectedDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereOrderedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePoNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPurchaseOrder {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $grocery_item_id
 * @property int $measurement_unit_id
 * @property numeric $ordered_quantity
 * @property numeric $received_quantity
 * @property numeric $unit_price
 * @property numeric $total_price
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\MeasurementUnit|null $measurementUnit
 * @property-read \App\Models\PurchaseOrder|null $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereMeasurementUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereOrderedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereReceivedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPurchaseOrderItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $menu_item_id
 * @property int|null $item_variant_id
 * @property int $grocery_item_id
 * @property int $measurement_unit_id
 * @property numeric $quantity_required
 * @property int $version
 * @property bool $is_current
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\MeasurementUnit|null $measurementUnit
 * @property-read \App\Models\MenuItem|null $menuItem
 * @property-read \App\Models\ItemVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereIsCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereItemVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereMeasurementUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereQuantityRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRecipe {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $menu_item_id
 * @property int $recipe_id
 * @property array<array-key, mixed> $snapshot
 * @property int $changed_by
 * @property string|null $change_reason
 * @property \Illuminate\Support\Carbon $effective_from
 * @property \Illuminate\Support\Carbon|null $effective_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\MenuItem|null $menuItem
 * @property-read \App\Models\User $modifier
 * @property-read \App\Models\Recipe|null $recipe
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereChangeReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereEffectiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereEffectiveUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecipeVersion whereSnapshot($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRecipeVersion {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $payment_id
 * @property int $restaurant_id
 * @property int|null $credit_note_id
 * @property numeric $amount
 * @property string $reason
 * @property string $refund_method
 * @property int $processed_by
 * @property int|null $approval_request_id
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\ApprovalRequest|null $approvalRequest
 * @property-read \App\Models\CreditNote|null $creditNote
 * @property-read \App\Models\Payment $payment
 * @property-read \App\Models\User $processor
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereApprovalRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCreditNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRefundMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereRestaurantId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRefund {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $logo_path
 * @property string|null $address
 * @property string|null $phone_no
 * @property string|null $gst_no
 * @property string|null $upi_id
 * @property string $subscription_plan
 * @property array<array-key, mixed>|null $features
 * @property array<array-key, mixed>|null $settings
 * @property int $user_limits
 * @property int $table_limits
 * @property int $rooms_limit
 * @property int $max_branches
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Branch> $branches
 * @property-read int|null $branches_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereGstNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereMaxBranches($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant wherePhoneNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereRoomsLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereSubscriptionPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereTableLimits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereUpiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Restaurant whereUserLimits($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRestaurant {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property int $capacity
 * @property string $qr_token
 * @property string|null $qr_image_path
 * @property string $status
 * @property int|null $table_group_id
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomerSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read \App\Models\TableGroup|null $tableGroup
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereQrImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereQrToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereTableGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRestaurantTable {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property string $room_number
 * @property int|null $floor
 * @property int $capacity
 * @property numeric $rate_per_night
 * @property string $qr_token
 * @property string|null $qr_image_path
 * @property string $status
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomerSession> $sessions
 * @property-read int|null $sessions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereFloor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereQrImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereQrToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRatePerNight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRoom {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property string $shift_type
 * @property int $started_by
 * @property int|null $ended_by
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\CashDrawer|null $cashDrawer
 * @property-read \App\Models\User|null $ender
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomerSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShiftStaff> $staff
 * @property-read int|null $staff_count
 * @property-read \App\Models\User $starter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereEndedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereShiftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereStartedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShift {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $shift_id
 * @property int $user_id
 * @property int|null $role_id
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property \Illuminate\Support\Carbon|null $checked_out_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Shift $shift
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereCheckedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftStaff whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShiftStaff {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $from_branch_id
 * @property int $to_branch_id
 * @property string $status
 * @property int $transferred_by
 * @property int|null $received_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch $fromBranch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockTransferItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $receiver
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\User $sender
 * @property-read \App\Models\Branch $toBranch
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereFromBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereToBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereTransferredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransfer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStockTransfer {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $stock_transfer_id
 * @property int $grocery_item_id
 * @property numeric $quantity
 * @property numeric $received_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\StockTransfer $stockTransfer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereReceivedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereStockTransferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockTransferItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStockTransferItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property string $name
 * @property string|null $contact_person
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $gst_number
 * @property string|null $payment_terms
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GroceryItem> $groceryItems
 * @property-read int|null $grocery_items_count
 * @property-read \App\Models\Restaurant $restaurant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereGstNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSupplier {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property int $merged_by
 * @property int|null $customer_session_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\CustomerSession|null $customerSession
 * @property-read \App\Models\User $merger
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RestaurantTable> $tables
 * @property-read int|null $tables_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereCustomerSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereMergedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTableGroup {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $customer_session_id
 * @property int|null $from_table_id
 * @property int|null $to_table_id
 * @property int $transferred_by
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\CustomerSession $customerSession
 * @property-read \App\Models\RestaurantTable|null $fromTable
 * @property-read \App\Models\User $operator
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\RestaurantTable|null $toTable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereCustomerSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereFromTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereToTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TableTransferLog whereTransferredBy($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTableTransferLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $restaurant_id
 * @property int|null $branch_id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_super_admin
 * @property bool $is_active
 * @property int $total_served
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Branch> $branches
 * @property-read int|null $branches_count
 * @property-read \App\Models\Branch|null $currentBranch
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Restaurant|null $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsSuperAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTotalServed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $restaurant_table_id
 * @property int $assigned_by
 * @property \Illuminate\Support\Carbon|null $assigned_at
 * @property \Illuminate\Support\Carbon|null $released_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $assigner
 * @property-read \App\Models\RestaurantTable|null $table
 * @property-read \App\Models\User $waiter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereAssignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereAssignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereRestaurantTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaiterTableAssignment whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperWaiterTableAssignment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $branch_id
 * @property int $grocery_item_id
 * @property int $measurement_unit_id
 * @property numeric $quantity
 * @property numeric|null $unit_cost
 * @property numeric|null $total_cost
 * @property string $reason
 * @property string|null $reason_notes
 * @property int $recorded_by
 * @property int|null $shift_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\GroceryItem|null $groceryItem
 * @property-read \App\Models\MeasurementUnit|null $measurementUnit
 * @property-read \App\Models\User $operator
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereGroceryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereMeasurementUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereReasonNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WasteRecord whereUnitCost($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperWasteRecord {}
}

