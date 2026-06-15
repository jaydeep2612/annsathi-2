<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class ApprovalService
{
    /**
     * Create a new approval request for a high-risk action.
     */
    public function createRequest(array $data): ApprovalRequest
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            $request = ApprovalRequest::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'requested_by' => $data['requested_by'] ?? auth()->id(),
                'entity_type' => $data['entity_type'], // Polymorphic target class
                'entity_id' => $data['entity_id'],     // Polymorphic target ID
                'action' => $data['action'],           // e.g. 'refund', 'purchase_order', 'waste', 'void_invoice'
                'reason' => $data['reason'],
                'status' => 'pending',
            ]);

            event(new \App\Events\ApprovalRequested($request));

            return $request;
        });
    }

    /**
     * Approve a pending request.
     */
    public function approveRequest(int $requestId, ?int $approvedBy = null): ApprovalRequest
    {
        return DB::transaction(function () use ($requestId, $approvedBy) {
            $request = ApprovalRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'pending') {
                throw new Exception("Approval request is already resolved as {$request->status}.");
            }

            $approverId = $approvedBy ?: auth()->id();
            $approver = \App\Models\User::find($approverId);
            if (!$approver || (!$approver->is_super_admin && !$approver->hasAnyRole(['manager', 'restaurant-admin']))) {
                throw new Exception("Only managers are allowed to approve requests.");
            }

            $request->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Execute post-approval operations based on action type
            $this->executePostApprovalAction($request);

            event(new \App\Events\ApprovalApproved($request));

            return $request;
        });
    }

    /**
     * Reject a pending request.
     */
    public function rejectRequest(int $requestId, ?int $rejectedBy = null): ApprovalRequest
    {
        return DB::transaction(function () use ($requestId, $rejectedBy) {
            $request = ApprovalRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'pending') {
                throw new Exception("Approval request is already resolved as {$request->status}.");
            }

            $rejecterId = $rejectedBy ?: auth()->id();
            $rejecter = \App\Models\User::find($rejecterId);
            if (!$rejecter || (!$rejecter->is_super_admin && !$rejecter->hasAnyRole(['manager', 'restaurant-admin']))) {
                throw new Exception("Only managers are allowed to reject requests.");
            }

            $request->update([
                'status' => 'rejected',
                'approved_by' => $rejecterId,
                'approved_at' => now(), // Rejection date recorded under approved_at for simplicity
            ]);

            return $request;
        });
    }

    /**
     * Trigger business workflows after a request is approved.
     */
    protected function executePostApprovalAction(ApprovalRequest $request): void
    {
        $entity = $request->entity;
        if (!$entity) {
            return;
        }

        switch ($request->action) {
            case 'purchase_order':
                // Transition PO status from draft to sent/approved
                if (method_exists($entity, 'update') && $entity->status === 'draft') {
                    $entity->update(['status' => 'sent']);
                }
                break;

            case 'void_invoice':
                // Auto-generate a credit note for the voided invoice
                if ($entity instanceof \App\Models\Invoice) {
                    app(BillingService::class)->issueCreditNote(
                        $entity, 
                        $request->reason, 
                        $entity->grand_total, 
                        $request->approved_by
                    );
                }
                break;

            default:
                // Other workflows (like refunds) check the approval status during execution rather than asynchronously
                break;
        }
    }
}
