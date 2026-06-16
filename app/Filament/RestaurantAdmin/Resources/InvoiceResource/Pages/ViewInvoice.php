<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\InvoiceResource\Pages;

use App\Filament\RestaurantAdmin\Resources\InvoiceResource;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;
}
