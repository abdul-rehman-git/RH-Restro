<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_products_index_shows_newest_products_first_by_default(): void
    {
        $user = User::factory()->create();

        $olderProduct = $this->createProduct('Older Product', 1, now()->subDay());
        $newerProduct = $this->createProduct('Newest Product', 99, now());

        $response = $this
            ->actingAs($user)
            ->get('/products');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->where('products.data.0.title', $newerProduct->title)
                ->where('products.data.1.title', $olderProduct->title));
    }

    private function createProduct(string $title, int $sortOrder, \DateTimeInterface $createdAt): Product
    {
        $product = Product::query()->create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'price' => 100,
            'sort_order' => $sortOrder,
            'stock_quantity' => 5,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $product->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $product->fresh();
    }
}
