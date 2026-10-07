<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCustomerController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Customers/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'date'   => $request->string('date')->toString(),
            ],
            'customers' => Customer::query()
                ->search($request->string('search')->toString())
                ->joinedDate($request->string('date')->toString())
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Customer $customer): array => [
                    'id'         => $customer->id,
                    'name'       => $customer->name,
                    'email'      => $customer->email,
                    'phone'      => $customer->phone,
                    'created_at' => $customer->created_at?->format('M d, Y'),
                ]),
        ]);
    }

    public function show(Customer $customer, Request $request): Response
    {
        $orders = $customer->orders()
            ->with('payment')
            ->filterStatus($request->string('status')->toString())
            ->filterDate($request->string('date')->toString())
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($order) => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'status_label' => $order->status_label,
                'total_amount' => $order->total_amount,
                'created_at'   => $order->created_at?->format('M d, Y'),
                'payment'      => $order->payment ? [
                    'status'       => $order->payment->status,
                    'status_label' => $order->payment->status_label,
                ] : null,
            ]);

        return Inertia::render('Customers/Show', [
            'customer' => [
                'id'         => $customer->id,
                'name'       => $customer->name,
                'email'      => $customer->email,
                'phone'      => $customer->phone,
                'created_at' => $customer->created_at?->format('M d, Y'),
            ],
            'orders'   => $orders,
            'filters'  => [
                'status' => $request->string('status')->toString(),
                'date'   => $request->string('date')->toString(),
            ],
        ]);
    }
}
