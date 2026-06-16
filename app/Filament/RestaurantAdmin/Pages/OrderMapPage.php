<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\KitchenRoutingService;
use App\Domains\Printing\Services\PrinterRoutingService;
use Filament\Notifications\Notification;

class OrderMapPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'Live Order Board';
    protected static ?string $navigationGroup = 'POS & Orders';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.restaurant-admin.pages.order-map-page';

    public function getActiveOrders()
    {
        return Order::whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
            ->with(['orderItems', 'customerSession.sessionable'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function confirmOrder(int $orderId): void
    {
        try {
            $order = Order::findOrFail($orderId);
            app(OrderService::class)->confirmOrder($orderId);
            
            // Route to kitchen stations and print KOT
            app(KitchenRoutingService::class)->routeOrderToKitchen($order);
            app(PrinterRoutingService::class)->routeOrderKOT($order);

            Notification::make()
                ->title('Order Confirmed')
                ->body('Sent to KDS and Kitchen Printer.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function startPreparing(int $orderId): void
    {
        try {
            app(OrderService::class)->startPreparing($orderId);
            Notification::make()
                ->title('Order is now Preparing')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markReady(int $orderId): void
    {
        try {
            app(OrderService::class)->markReady($orderId);
            Notification::make()
                ->title('Order is Ready to Serve')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function serveOrder(int $orderId): void
    {
        try {
            app(OrderService::class)->serveOrder($orderId);
            Notification::make()
                ->title('Order Served')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelOrder(int $orderId): void
    {
        try {
            app(OrderService::class)->cancelOrder($orderId, 'Cancelled from Live Order Board');
            Notification::make()
                ->title('Order Cancelled')
                ->warning()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function printReceipt(int $orderId): void
    {
        try {
            $order = Order::findOrFail($orderId);
            app(PrinterRoutingService::class)->routeOrderReceipt($order);
            Notification::make()
                ->title('Receipt sent to billing printer')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Printing Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
