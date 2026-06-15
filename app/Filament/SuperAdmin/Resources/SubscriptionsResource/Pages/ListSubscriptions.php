<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\SubscriptionsResource\Pages;

use App\Filament\SuperAdmin\Resources\SubscriptionsResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionsResource::class;
}
