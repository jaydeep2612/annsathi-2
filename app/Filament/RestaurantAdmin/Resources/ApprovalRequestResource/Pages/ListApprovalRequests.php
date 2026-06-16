<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ApprovalRequestResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ApprovalRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovalRequests extends ListRecords
{
    protected static string $resource = ApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
