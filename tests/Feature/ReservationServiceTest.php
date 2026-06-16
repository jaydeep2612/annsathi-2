<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\RestaurantTable;
use App\Models\Reservation;
use App\Models\CustomerSession;
use App\Domains\Reservations\Services\ReservationService;
use App\Domains\Reservations\Exceptions\ReservationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Exception;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;
    protected $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Taste Palace',
            'slug' => 'taste-palace',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Downtown Branch',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->table = RestaurantTable::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Table 1',
            'capacity' => 4,
            'qr_token' => 'TABLE-1-QR',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Test successful reservation creation.
     */
    public function test_create_reservation_success(): void
    {
        $service = app(ReservationService::class);

        $reservation = $service->createReservation([
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'Alice',
            'customer_phone' => '1234567890',
            'reservation_time' => Carbon::now()->addDay()->setHour(19)->setMinute(0)->toDateTimeString(),
            'pax_count' => 2,
            'duration_minutes' => 120,
            'status' => 'confirmed',
        ]);

        $this->assertNotNull($reservation);
        $this->assertEquals('Alice', $reservation->customer_name);
        $this->assertEquals('confirmed', $reservation->status);
        $this->assertEquals('reserved', $this->table->fresh()->status);
    }

    /**
     * Test reservation capacity check.
     */
    public function test_create_reservation_capacity_exceeded(): void
    {
        $service = app(ReservationService::class);

        $this->expectException(ReservationException::class);
        $this->expectExceptionMessage('exceeds table capacity');

        $service->createReservation([
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'Bob Group',
            'customer_phone' => '1234567890',
            'reservation_time' => Carbon::now()->addDay()->setHour(19)->setMinute(0)->toDateTimeString(),
            'pax_count' => 6, // Capacity is 4
            'duration_minutes' => 120,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test reservation overlap validation.
     */
    public function test_create_reservation_overlap(): void
    {
        $service = app(ReservationService::class);

        // Book slot 19:00 - 21:00
        $service->createReservation([
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'First Guest',
            'customer_phone' => '1234567890',
            'reservation_time' => Carbon::now()->addDay()->setHour(19)->setMinute(0)->toDateTimeString(),
            'pax_count' => 2,
            'duration_minutes' => 120,
            'status' => 'confirmed',
        ]);

        // Attempt overlap at 20:00 - 22:00 (overlaps with 19:00 - 21:00)
        $this->expectException(ReservationException::class);
        $this->expectExceptionMessage('is already booked or occupied');

        $service->createReservation([
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'Second Guest',
            'customer_phone' => '0987654321',
            'reservation_time' => Carbon::now()->addDay()->setHour(20)->setMinute(0)->toDateTimeString(),
            'pax_count' => 2,
            'duration_minutes' => 120,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test confirmation workflow.
     */
    public function test_confirm_reservation(): void
    {
        $service = app(ReservationService::class);

        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'Charlie',
            'customer_phone' => '1112223333',
            'reservation_time' => Carbon::now()->addDay()->toDateTimeString(),
            'pax_count' => 3,
            'status' => 'pending',
        ]);

        $service->confirmReservation($reservation->id);

        $this->assertEquals('confirmed', $reservation->fresh()->status);
        $this->assertEquals('reserved', $this->table->fresh()->status);
    }

    /**
     * Test seating a reservation.
     */
    public function test_seat_reservation(): void
    {
        $service = app(ReservationService::class);

        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'Diana',
            'customer_phone' => '4445556666',
            'reservation_time' => Carbon::now()->toDateTimeString(),
            'pax_count' => 4,
            'status' => 'confirmed',
        ]);

        // Seat the reservation
        $service->seatReservation($reservation->id);

        $this->assertEquals('seated', $reservation->fresh()->status);
        $this->assertEquals('occupied', $this->table->fresh()->status);

        // Assert customer session was started
        $this->assertDatabaseHas('customer_sessions', [
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'session_type' => 'table',
            'sessionable_id' => $this->table->id,
            'customer_name' => 'Diana',
            'status' => 'active',
        ]);
    }

    /**
     * Test cancelling a reservation.
     */
    public function test_cancel_reservation(): void
    {
        $service = app(ReservationService::class);

        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'restaurant_table_id' => $this->table->id,
            'customer_name' => 'Edward',
            'customer_phone' => '7778889999',
            'reservation_time' => Carbon::now()->addDay()->toDateTimeString(),
            'pax_count' => 2,
            'status' => 'confirmed',
        ]);

        $this->table->update(['status' => 'reserved']);

        $service->cancelReservation($reservation->id);

        $this->assertEquals('cancelled', $reservation->fresh()->status);
        $this->assertEquals('available', $this->table->fresh()->status);
    }
}
