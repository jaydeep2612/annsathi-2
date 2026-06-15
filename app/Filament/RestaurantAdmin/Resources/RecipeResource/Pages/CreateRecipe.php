<?php

namespace App\Filament\RestaurantAdmin\Resources\RecipeResource\Pages;

use App\Filament\RestaurantAdmin\Resources\RecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;
}
