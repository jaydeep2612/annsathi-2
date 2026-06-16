<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PaymentResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;
}
