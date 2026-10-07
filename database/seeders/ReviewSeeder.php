<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Seed the application's review records.
     */
    public function run(): void
    {
        Review::query()->delete();

        $products = Product::query()->get()->keyBy('slug');

        $reviews = [
            [
                'product_slug' => 'prime-angus-ribeye-steak',
                'reviewer_name' => 'Alexander Hayes',
                'rating' => 5,
                'comment' => 'The Angus Ribeye was cooked to a flawless medium rare. Tender, smoky crust from the charbroil, and the truffle potato mash melted in my mouth. Easily the finest steakhouse dining experience in town.',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                ],
                'reviewed_at' => now()->subDays(3)->toDateString(),
                'helpful_count' => 38,
                'is_featured' => true,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
            [
                'product_slug' => 'double-truffle-smashed-burger',
                'reviewer_name' => 'Sophia Martinez',
                'rating' => 5,
                'comment' => 'Hands down the juiciest smash burger I have ever tasted! The lace-crisp edges, caramelized balsamic onions, and rich truffle aioli created the perfect bite. Will definitely be ordering regularly.',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                ],
                'reviewed_at' => now()->subDays(5)->toDateString(),
                'helpful_count' => 29,
                'is_featured' => true,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
            [
                'product_slug' => 'wood-fired-margherita-pizza',
                'reviewer_name' => 'Marco Bellini',
                'rating' => 5,
                'comment' => 'Authentic wood-fired mastery. The 48-hour fermented crust had that airy, blistered char you usually only find in Naples. Fresh basil and sweet San Marzano tomatoes made every slice heavenly.',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=800&q=80',
                ],
                'reviewed_at' => now()->subDays(7)->toDateString(),
                'helpful_count' => 44,
                'is_featured' => true,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
            [
                'product_slug' => null,
                'reviewer_name' => 'Amina & Farhan Khan',
                'rating' => 5,
                'comment' => 'We celebrated our anniversary at RH Restro last weekend. The warm candlelight ambiance, attentive hospitality, and sizzling BBQ platter made our evening truly unforgettable. A must-visit culinary gem!',
                'gallery_images' => [],
                'reviewed_at' => now()->subDays(10)->toDateString(),
                'helpful_count' => 52,
                'is_featured' => true,
                'is_approved' => true,
                'is_verified_purchase' => false,
            ],
            [
                'product_slug' => 'pan-seared-norwegian-salmon',
                'reviewer_name' => 'Dr. Julian Vance',
                'rating' => 5,
                'comment' => 'The salmon skin was extraordinarily crispy while the fillet stayed silky-soft and succulent. Paired wonderfully with the citrus asparagus and lemon butter sauce. Very healthy yet luxurious.',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80',
                ],
                'reviewed_at' => now()->subDays(12)->toDateString(),
                'helpful_count' => 21,
                'is_featured' => true,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
            [
                'product_slug' => 'warm-belgian-molten-lava-cake',
                'reviewer_name' => 'Chloe Davenport',
                'rating' => 5,
                'comment' => 'Decadent dark Belgian chocolate flowing right from the center, balanced by cold Madagascar vanilla bean ice cream. An absolute dream dessert to end the dinner.',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80',
                ],
                'reviewed_at' => now()->subDays(14)->toDateString(),
                'helpful_count' => 31,
                'is_featured' => true,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
            [
                'product_slug' => 'grand-sizzling-bbq-platter',
                'reviewer_name' => 'Tariq Mansoor',
                'rating' => 5,
                'comment' => 'The aroma when this platter arrived sizzling at our table was incredible! Tender seekh kebabs, smoky malai boti, and charbroiled lamb chops. Perfect for sharing with friends.',
                'gallery_images' => [],
                'reviewed_at' => now()->subDays(18)->toDateString(),
                'helpful_count' => 17,
                'is_featured' => false,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
            [
                'product_slug' => 'fettuccine-alfredo-tartufo',
                'reviewer_name' => 'Isabella Rossi',
                'rating' => 5,
                'comment' => 'Velvety rich cream, freshly grated 24-month Parmigiano-Reggiano, and subtle black truffle oil. Fresh handmade pasta cooked al dente to perfection.',
                'gallery_images' => [],
                'reviewed_at' => now()->subDays(21)->toDateString(),
                'helpful_count' => 19,
                'is_featured' => false,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ],
        ];

        foreach ($reviews as $review) {
            $product = $review['product_slug'] ? $products->get($review['product_slug']) : null;

            Review::updateOrCreate(
                [
                    'reviewer_name' => $review['reviewer_name'],
                    'comment' => $review['comment'],
                ],
                [
                    'product_id' => $product?->id,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'gallery_images' => $review['gallery_images'] ?? [],
                    'reviewed_at' => $review['reviewed_at'],
                    'helpful_count' => $review['helpful_count'],
                    'is_featured' => $review['is_featured'],
                    'is_approved' => $review['is_approved'],
                    'is_verified_purchase' => $review['is_verified_purchase'],
                ],
            );
        }
    }
}
