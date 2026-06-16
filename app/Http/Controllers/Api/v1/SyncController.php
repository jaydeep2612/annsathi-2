<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\OfflineSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SyncController extends BaseApiController
{
    public function __construct(protected OfflineSyncService $syncService)
    {
    }

    /**
     * Push offline actions to sync queue and trigger processing.
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'actions' => 'required|array',
            'actions.*.action_type' => 'required|string|in:create_order,create_payment,create_reservation,sync_sale',
            'actions.*.payload' => 'required|array',
            'actions.*.device_identifier' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        try {
            $actions = $request->input('actions');
            $pushedActions = [];

            foreach ($actions as $actionData) {
                $pushedActions[] = $this->syncService->pushOfflineAction($actionData);
            }

            // Trigger sync queue processing immediately
            $processResults = $this->syncService->processSyncQueue();

            return $this->successResponse([
                'pushed_count' => count($pushedActions),
                'sync_results' => $processResults,
            ], 'Sync operations processed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
