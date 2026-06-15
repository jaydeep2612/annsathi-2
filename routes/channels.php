<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast channel for restaurant wide updates
Broadcast::channel('restaurant.{restaurantId}', function ($user, $restaurantId) {
    return (int) $user->restaurant_id === (int) $restaurantId;
});

// Broadcast channel for KDS (Kitchen Display System)
Broadcast::channel('restaurant.{restaurantId}.branch.{branchId}.kitchen', function ($user, $restaurantId, $branchId) {
    return (int) $user->restaurant_id === (int) $restaurantId 
        && ($user->is_super_admin || $user->branches()->where('branches.id', $branchId)->exists() || $user->branch_id === (int) $branchId);
});

// Broadcast channel for real-time Waiter Calls and Manager alerts
Broadcast::channel('restaurant.{restaurantId}.branch.{branchId}.alerts', function ($user, $restaurantId, $branchId) {
    return (int) $user->restaurant_id === (int) $restaurantId 
        && ($user->is_super_admin || $user->branches()->where('branches.id', $branchId)->exists() || $user->branch_id === (int) $branchId);
});

// Broadcast channel for table/room session updates and bill requests
Broadcast::channel('restaurant.{restaurantId}.branch.{branchId}.sessions', function ($user, $restaurantId, $branchId) {
    return (int) $user->restaurant_id === (int) $restaurantId 
        && ($user->is_super_admin || $user->branches()->where('branches.id', $branchId)->exists() || $user->branch_id === (int) $branchId);
});
