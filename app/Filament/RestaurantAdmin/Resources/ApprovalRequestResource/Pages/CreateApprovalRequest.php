<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ApprovalRequestResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ApprovalRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApprovalRequest extends CreateRecord
{
    protected static string $resource = ApprovalRequestResource::class;
}
