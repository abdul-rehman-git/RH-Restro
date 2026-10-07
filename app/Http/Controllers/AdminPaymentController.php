<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $payments = Payment::query()
            ->with(['order', 'customer'])
            ->filterStatus($request->string('status')->toString())
            ->filterDate($request->string('date')->toString())
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Payment $payment): array => [
                'id'           => $payment->id,
                'order_id'     => $payment->order?->id,
                'order_number' => $payment->order?->order_number,
                'customer'     => [
                    'name'  => $payment->customer?->name,
                    'email' => $payment->customer?->email,
                ],
                'amount'       => $payment->amount,
                'method'       => $payment->method,
                'status'       => $payment->status,
                'status_label' => $payment->status_label,
                'created_at'   => $payment->created_at?->format('M d, Y'),
            ]);

        return Inertia::render('Payments/Index', [
            'filters' => [
                'status' => $request->string('status')->toString(),
                'date'   => $request->string('date')->toString(),
            ],
            'payments' => $payments,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,refunded'],
        ]);

        $payment->update($validated);

        return back()->with('success', 'Payment status updated.');
    }
}
