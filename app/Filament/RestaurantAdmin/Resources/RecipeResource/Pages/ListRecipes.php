<?php

namespace App\Filament\RestaurantAdmin\Resources\RecipeResource\Pages;

use App\Filament\RestaurantAdmin\Resources\RecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecipes extends ListRecords
{
    protected static string $resource = RecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
