<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPayloads;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WishlistItem;
use App\Services\ImageUploadService;
use App\Support\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    use BuildsPublicPayloads;

    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();

        $totalOrders = $customer->orders()->count();
        $pendingOrders = $customer->orders()->where('status', 'pending')->count();
        $completedOrders = $customer->orders()->where('status', 'delivered')->count();
        $wishlistCount = $customer->wishlistItems()->count();
        $totalSpent = (float) $customer->payments()->where('status', 'paid')->sum('amount');
        $lastOrder = $customer->orders()->latest()->first();
        $recentOrders = $customer->orders()->latest()->limit(5)->get();
        $recentInvoices = $customer->payments()->with('order')->latest()->limit(5)->get();

        return Inertia::render('Customer/Dashboard', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('my-account'),
            'stats' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'wishlist_count' => $wishlistCount,
                'total_spent' => $totalSpent,
                'last_order' => $lastOrder ? [
                    'id' => $lastOrder->id,
                    'order_number' => $lastOrder->order_number,
                    'status' => $lastOrder->status,
                    'status_label' => $lastOrder->status_label,
                    'total_amount' => (float) $lastOrder->total_amount,
                    'created_at' => $lastOrder->created_at->format('M d, Y'),
                ] : null,
            ],
            'recentOrders' => $recentOrders->map(fn(Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'total_amount' => (float) $order->total_amount,
                'created_at' => $order->created_at->format('M d, Y'),
            ]),
            'recentInvoices' => $recentInvoices->map(fn(Payment $payment) => [
                'id' => $payment->id,
                'order_number' => $payment->order?->order_number,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'status_label' => $payment->status_label,
                'created_at' => $payment->created_at->format('M d, Y'),
            ]),
        ]);
    }

    public function orders(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $perPage = $request->integer('per_page', 10);

        $orders = $customer->orders()
            ->with(['orderItems', 'payment'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'orders' => [
                'data' => $orders->getCollection()->map(fn(Order $order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'total_amount' => (float) $order->total_amount,
                    'created_at' => $order->created_at->format('M d, Y'),
                    'items' => $order->orderItems->map(fn($item) => [
                        'id' => $item->id,
                        'product_title' => $item->product_title,
                        'product_price' => (float) $item->product_price,
                        'quantity' => $item->quantity,
                        'subtotal' => (float) $item->subtotal,
                    ]),
                    'payment' => $order->payment ? [
                        'id' => $order->payment->id,
                        'method' => $order->payment->method,
                        'status' => $order->payment->status,
                        'status_label' => $order->payment->status_label,
                        'amount' => (float) $order->payment->amount,
                    ] : null,
                ])->values()->all(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function wishlistProducts(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $items = WishlistItem::where('customer_id', $customer->id)
            ->with('product.category')
            ->latest()
            ->get();

        return response()->json([
            'products' => $items->filter(fn($item) => $item->product)->map(fn($item) => [
                'id' => $item->product->id,
                'slug' => $item->product->slug,
                'title' => $item->product->title,
                'price' => (float) $item->product->price,
                'image' => $item->product->imageUrl(),
                'category' => $item->product->category?->name,
                'in_stock' => $item->product->stock_quantity > 0,
                'wishlist_item_id' => $item->id,
            ])->values()->all(),
        ]);
    }

    public function invoices(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $perPage = $request->integer('per_page', 10);

        $payments = $customer->payments()
            ->with('order')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'invoices' => [
                'data' => $payments->getCollection()->map(fn(Payment $payment) => [
                    'id' => $payment->id,
                    'order_number' => $payment->order?->order_number,
                    'amount' => (float) $payment->amount,
                    'method' => $payment->method,
                    'status' => $payment->status,
                    'status_label' => $payment->status_label,
                    'created_at' => $payment->created_at->format('M d, Y'),
                    'order_status' => $payment->order?->status_label,
                ])->values()->all(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function updateProfile(Request $request, ImageUploadService $uploads)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'profile_photo.max' => 'Image size cannot be more than 2MB.',
        ]);

        $customer->name = $validated['name'];
        $customer->phone = $validated['phone'] ?? null;

        if ($request->hasFile('profile_photo')) {
            $uploads->delete([
                'cloudinary_public_id' => $customer->profile_photo_cloudinary_public_id,
                'uploaded_image_id' => $customer->profile_photo_uploaded_image_id,
            ]);

            $photo = $uploads->upload($request->file('profile_photo'), 'profile_photo', 'customer-profile-photo');
            $customer->profile_photo_url = $photo['url'];
            $customer->profile_photo_cloudinary_public_id = $photo['cloudinary_public_id'];
            $customer->profile_photo_is_uploaded_to_cloudinary = $photo['is_uploaded_to_cloudinary'];
            $customer->profile_photo_uploaded_image_id = $photo['uploaded_image_id'];
            $customer->profile_photo_path = null;
        }

        $customer->save();

        if (isset($photo)) {
            $uploads->queueCloudinaryUpload($photo['uploaded_image_id']);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'profile_photo_url' => $customer->profilePhotoUrl(),
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The current password is incorrect.'),
            ]);
        }

        $customer->password = Hash::make($validated['new_password']);
        $customer->save();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
