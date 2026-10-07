<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        // Order statistics
        $orderStats = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Customer statistics
        $customerStats = [
            'all' => Customer::count(),
            'with_orders' => Customer::whereHas('orders')->count(),
            'completed_orders' => Customer::whereHas('orders', function ($q) {
                $q->where('status', 'delivered');
            })->count(),
        ];

        // Payment statistics
        $paymentStats = [
            'all' => Payment::count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'paid' => Payment::where('status', 'paid')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
        ];

        // Sales summary
        $salesSummary = [
            'total_sales' => Payment::where('status', 'paid')->sum('amount'),
            'total_refunded' => Payment::where('status', 'refunded')->sum('amount'),
            'total_pending' => Payment::where('status', 'pending')->sum('amount'),
        ];

        // Weekly sales data for chart
        $weeklySales = Order::selectRaw('DATE(created_at) as date, DAYNAME(created_at) as day, SUM(total_amount) as sales')
            ->whereBetween('created_at', [now()->subDays(6), now()])
            ->where('status', 'delivered')
            ->groupByRaw('DATE(created_at), DAYNAME(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn ($item) => [
                'label' => substr($item->day, 0, 3),
                'sales' => (int) $item->sales,
            ])
            ->toArray();

        // Ensure 7 days of data
        if (count($weeklySales) < 7) {
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $startDate = now()->subDays(6)->startOfDay();
            
            $weeklySales = collect($days)->map(function ($day, $index) use ($startDate) {
                $date = $startDate->copy()->addDays($index);
                return [
                    'label' => $day,
                    'sales' => Order::whereDate('created_at', $date)
                        ->where('status', 'delivered')
                        ->sum('total_amount'),
                ];
            })->toArray();
        }

        // Monthly orders trend
        $monthlyOrders = Order::selectRaw('WEEK(created_at) as week, COUNT(*) as orders')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('WEEK(created_at)')
            ->orderByRaw('WEEK(created_at) DESC')
            ->limit(4)
            ->get()
            ->map(fn ($item) => [
                'label' => 'Week ' . $item->week,
                'orders' => $item->orders,
            ])
            ->reverse()
            ->values()
            ->toArray();

        return response()->json([
            'order_stats' => $orderStats,
            'customer_stats' => $customerStats,
            'payment_stats' => $paymentStats,
            'sales_summary' => $salesSummary,
            'weekly_sales' => $weeklySales,
            'monthly_orders' => $monthlyOrders,
        ]);
    }
}
