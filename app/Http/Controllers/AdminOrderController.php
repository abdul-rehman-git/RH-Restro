<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with('customer')
            ->search($request->string('search')->toString())
            ->filterStatus($request->string('status')->toString())
            ->filterDate($request->string('date')->toString())
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Order $order): array => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'customer'     => [
                    'id'    => $order->customer?->id,
                    'name'  => $order->customer?->name,
                    'email' => $order->customer?->email,
                    'phone' => $order->customer?->phone,
                ],
                'status'       => $order->status,
                'status_label' => $order->status_label,
                'total_amount' => $order->total_amount,
                'created_at'   => $order->created_at?->format('M d, Y'),
            ]);

        return Inertia::render('Orders/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'date'   => $request->string('date')->toString(),
            ],
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['customer', 'orderItems', 'payment']);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'status_label' => $order->status_label,
                'total_amount' => $order->total_amount,
                'notes'        => $order->notes,
                'created_at'   => $order->created_at?->format('M d, Y H:i'),
                'customer'     => [
                    'id'    => $order->customer?->id,
                    'name'  => $order->customer?->name,
                    'email' => $order->customer?->email,
                    'phone' => $order->customer?->phone,
                ],
                'items' => $order->orderItems->map(fn ($item) => [
                    'id'            => $item->id,
                    'product_title' => $item->product_title,
                    'product_price' => $item->product_price,
                    'quantity'      => $item->quantity,
                    'subtotal'      => $item->subtotal,
                ]),
                'payment' => $order->payment ? [
                    'id'           => $order->payment->id,
                    'amount'       => $order->payment->amount,
                    'method'       => $order->payment->method,
                    'status'       => $order->payment->status,
                    'status_label' => $order->payment->status_label,
                    'created_at'   => $order->payment->created_at?->format('M d, Y H:i'),
                ] : null,
            ],
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,shipped,delivered,cancelled'],
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated.');
    }
}
