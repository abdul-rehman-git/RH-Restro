<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        Auth::guard('customer')->login($customer);

        return response()->json([
            'message' => 'Registration successful',
            'customer' => $customer,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login successful',
                'customer' => Auth::guard('customer')->user(),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function cartIndex(Request $request)
    {
        $cartItems = $request->user('customer')
            ->cartItems()
            ->with(['product.category', 'variant'])
            ->get()
            ->map(function ($item) {
                $price = $item->variant?->price ?? $item->product?->price ?? 0;
                $image = $item->variant?->imageUrl() ?: $item->product?->imageUrl();
                $stockQuantity = $item->variant
                    ? (int) $item->variant->stock_quantity
                    : (int) ($item->product?->stock_quantity ?? 0);

                return [
                    'id' => $item->id,
                    'customer_id' => $item->customer_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => (float) $price,
                    'image' => $image,
                    'stock_quantity' => $stockQuantity,
                    'in_stock' => $stockQuantity > 0,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'title' => $item->product->title,
                        'slug' => $item->product->slug,
                        'price' => (float) $price,
                        'image' => $image,
                        'stock_quantity' => (int) $item->product->stock_quantity,
                        'in_stock' => (int) $item->product->stock_quantity > 0,
                        'category' => $item->product->category ? [
                            'name' => $item->product->category->name,
                        ] : null,
                    ] : null,
                    'variant' => $item->variant ? [
                        'id' => $item->variant->id,
                        'name' => $item->variant->name,
                        'price' => (float) $item->variant->price,
                        'image' => $item->variant->imageUrl(),
                        'stock_quantity' => (int) $item->variant->stock_quantity,
                        'in_stock' => (int) $item->variant->stock_quantity > 0,
                    ] : null,
                ];
            });

        return response()->json(['cartItems' => $cartItems]);
    }

    public function cartAdd(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $customer = $request->user('customer');
        $variantId = $validated['product_variant_id'] ?? null;
        $addQuantity = $validated['quantity'] ?? 1;

        $product = \App\Models\Product::findOrFail($validated['product_id']);

        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'product' => ['This product is no longer available.'],
            ]);
        }

        $variant = null;
        if ($variantId) {
            $variant = \App\Models\ProductVariant::where('product_id', $product->id)->find($variantId);
            if (! $variant || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'product' => ['Selected product variant is no longer available.'],
                ]);
            }
        }

        $availableStock = $variant ? (int) $variant->stock_quantity : (int) $product->stock_quantity;

        if ($availableStock <= 0) {
            throw ValidationException::withMessages([
                'stock' => ['This product is currently out of stock.'],
            ]);
        }

        $cartItem = $customer->cartItems()
            ->where('product_id', $validated['product_id'])
            ->where('product_variant_id', $variantId)
            ->first();

        $existingQuantity = $cartItem ? (int) $cartItem->quantity : 0;
        $newTotalQuantity = $existingQuantity + $addQuantity;

        if ($newTotalQuantity > $availableStock) {
            if ($existingQuantity > 0) {
                $remaining = max(0, $availableStock - $existingQuantity);
                if ($remaining === 0) {
                    throw ValidationException::withMessages([
                        'quantity' => ["You already have the maximum available stock ({$availableStock} unit(s)) in your cart."],
                    ]);
                }
                throw ValidationException::withMessages([
                    'quantity' => ["You already have {$existingQuantity} unit(s) in cart. You can only add {$remaining} more."],
                ]);
            }

            throw ValidationException::withMessages([
                'quantity' => ["Cannot add {$addQuantity} item(s). Only {$availableStock} unit(s) available in stock."],
            ]);
        }

        if ($cartItem) {
            $cartItem->increment('quantity', $addQuantity);
        } else {
            $cartItem = $customer->cartItems()->create([
                'product_id' => $validated['product_id'],
                'product_variant_id' => $variantId,
                'quantity' => $addQuantity,
            ]);
        }

        return response()->json([
            'message' => 'Added to cart',
            'cartItem' => $cartItem->load(['product', 'variant']),
        ]);
    }

    public function cartUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem = $request->user('customer')->cartItems()->with(['product', 'variant'])->findOrFail($id);

        $availableStock = $cartItem->variant
            ? (int) $cartItem->variant->stock_quantity
            : (int) ($cartItem->product?->stock_quantity ?? 0);

        if ($availableStock <= 0) {
            throw ValidationException::withMessages([
                'stock' => ['This product is currently out of stock.'],
            ]);
        }

        if ($validated['quantity'] > $availableStock) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$availableStock} unit(s) available in stock."],
            ]);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Cart updated',
            'cartItem' => $cartItem->load(['product', 'variant']),
        ]);
    }

    public function cartRemove(Request $request, $id)
    {
        $cartItem = $request->user('customer')->cartItems()->findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Removed from cart']);
    }

    public function placeWhatsAppOrder(Request $request)
    {
        $customer = $request->user('customer');
        $cartItems = $customer->cartItems()->with(['product', 'variant'])->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        foreach ($cartItems as $item) {
            $title = $item->product?->title ?? 'Product';
            $variantName = $item->variant?->name;
            $fullName = $variantName ? "{$title} ({$variantName})" : $title;

            $stock = $item->variant
                ? (int) $item->variant->stock_quantity
                : (int) ($item->product?->stock_quantity ?? 0);

            if ($stock <= 0) {
                return response()->json([
                    'message' => "'{$fullName}' is currently out of stock.",
                ], 422);
            }

            if ($item->quantity > $stock) {
                return response()->json([
                    'message' => "Requested quantity for '{$fullName}' exceeds available stock ({$stock}).",
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($customer, $cartItems) {
            // Generate unique order number: ORD-YYYYMMDD-XXXX
            $datePart = now()->format('Ymd');
            $random   = strtoupper(substr(uniqid(), -4));
            $orderNumber = "ORD-{$datePart}-{$random}";

            // Ensure uniqueness
            while (Order::where('order_number', $orderNumber)->exists()) {
                $random      = strtoupper(substr(uniqid(), -4));
                $orderNumber = "ORD-{$datePart}-{$random}";
            }

            $total = $cartItems->reduce(function ($carry, $item) {
                $price = (float) ($item->variant?->price ?? $item->product?->price ?? 0);

                return $carry + ($price * $item->quantity);
            }, 0.0);

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id'  => $customer->id,
                'status'       => 'pending',
                'total_amount' => $total,
            ]);

            foreach ($cartItems as $item) {
                $price = (float) ($item->variant?->price ?? $item->product?->price ?? 0);
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_title'      => $item->product?->title ?? 'Unknown Product',
                    'variant_name'       => $item->variant?->name,
                    'product_price'      => $price,
                    'quantity'           => $item->quantity,
                    'subtotal'           => $price * $item->quantity,
                ]);
            }

            Payment::create([
                'order_id'    => $order->id,
                'customer_id' => $customer->id,
                'amount'      => $total,
                'method'      => 'whatsapp',
                'status'      => 'pending',
            ]);

            // Clear the cart
            $customer->cartItems()->delete();

            return $order;
        });

        return response()->json([
            'message'      => 'Order placed successfully.',
            'order_number' => $order->order_number,
            'order_id'     => $order->id,
        ]);
    }

    public function wishlistIndex(Request $request)
    {
        $wishlistIds = $request->user('customer')->wishlistItems()->pluck('product_id');

        return response()->json(['wishlistIds' => $wishlistIds]);
    }

    public function wishlistToggle(Request $request, $productId)
    {
        $customer = $request->user('customer');
        $wishlistItem = $customer->wishlistItems()->where('product_id', $productId)->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $action = 'removed';
        } else {
            $customer->wishlistItems()->create(['product_id' => $productId]);
            $action = 'added';
        }

        return response()->json([
            'message' => "Product {$action} to wishlist",
            'action' => $action,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:customers,email'],
        ]);

        $status = Password::broker('customers')->sendResetLink(
            $validated
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __('Password reset link sent to your email.')]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
