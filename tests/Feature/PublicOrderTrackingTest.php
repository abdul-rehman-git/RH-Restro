<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicOrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_order_tracking_page_renders(): void
    {
        $this->get('/order-tracking')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderTracking'));
    }

    public function test_public_order_tracking_api_returns_order_details_for_valid_order_number(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Tracking Customer',
            'email' => 'tracking@example.com',
            'password' => 'password123',
            'phone' => '+92-300-0000000',
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-20260516-AB12',
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'total_amount' => 4500,
            'notes' => 'Please call before delivery.',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_title' => 'Handmade Wall Art',
            'product_price' => 1500,
            'quantity' => 3,
            'subtotal' => 4500,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'amount' => 4500,
            'method' => 'whatsapp',
            'status' => 'pending',
        ]);

        $this->postJson('/api/public/order-tracking', [
            'order_number' => 'ord-20260516-ab12',
        ])
            ->assertOk()
            ->assertJsonPath('order.order_number', 'ORD-20260516-AB12')
            ->assertJsonPath('order.status', 'confirmed')
            ->assertJsonPath('order.status_label', 'Confirmed')
            ->assertJsonPath('order.items.0.title', 'Handmade Wall Art')
            ->assertJsonPath('order.items.0.quantity', 3)
            ->assertJsonPath('order.payment.status', 'pending')
            ->assertJsonPath('order.timeline.1.current', true);
    }

    public function test_public_order_tracking_api_returns_not_found_for_unknown_order_number(): void
    {
        $this->postJson('/api/public/order-tracking', [
            'order_number' => 'ORD-UNKNOWN-0000',
        ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'No order found for this order number. Please verify and try again.');
    }
}
