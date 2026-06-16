<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ApprovalRequestResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ApprovalRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditApprovalRequest extends EditRecord
{
    protected static string $resource = ApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
