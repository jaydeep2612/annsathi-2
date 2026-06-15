<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\PlansResource\Pages;

use App\Filament\SuperAdmin\Resources\PlansResource;
use Filament\Resources\Pages\EditRecord;

class EditPlans extends EditRecord
{
    protected static string $resource = PlansResource::class;
}
