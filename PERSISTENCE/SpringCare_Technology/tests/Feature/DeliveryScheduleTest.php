<?php

namespace Tests\Feature;

use App\Models\DeliverySchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_delivery_schedules_appear_on_the_homepage_and_hidden_ones_do_not(): void
    {
        DeliverySchedule::create([
            'city' => 'Lagos',
            'month' => 11,
            'year' => 2026,
            'is_published' => true,
        ]);
        DeliverySchedule::create([
            'city' => 'Enugu',
            'month' => 12,
            'year' => 2026,
            'is_published' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('DELIVERY ACROSS NIGERIA')
            ->assertSee('Lagos')
            ->assertSee('November 2026')
            ->assertDontSee('Enugu');
    }

    public function test_guests_cannot_access_delivery_schedule_management(): void
    {
        $this->get(backpack_url('delivery-schedule'))->assertRedirect();
        $this->get(backpack_url('delivery-schedule/create'))->assertRedirect();
    }
}