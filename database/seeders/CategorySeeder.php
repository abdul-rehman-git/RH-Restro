<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Starters & Appetizers',
                'slug' => 'starters-appetizers',
                'description' => 'Crispy golden calamari, loaded truffle parmesan fries, spicy glazed wings, and freshly baked herb breads.',
                'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => "Chef's Signature Mains",
                'slug' => 'signature-mains',
                'description' => 'Sizzling prime ribeye steaks, grilled Norwegian salmon, herb-infused lamb chops, and master chef creations.',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Gourmet Burgers',
                'slug' => 'gourmet-burgers',
                'description' => 'Double-smashed Angus beef patties, buttermilk crispy fried chicken, melted aged cheddar, and house-made truffle aioli on brioche.',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Artisanal Pizzas & Pastas',
                'slug' => 'pizzas-pastas',
                'description' => 'Stone-baked Neapolitan sourdough pizzas, creamy Fettuccine Alfredo, and spicy al dente Penne Arbiatta.',
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Sizzlers & BBQ Grills',
                'slug' => 'sizzlers-grills',
                'description' => 'Flaming sizzler platters, charbroiled skewers, and smoky grilled delicacies served on piping hot cast iron.',
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80',
                'sort_order' => 5,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Desserts & Sweets',
                'slug' => 'desserts-sweets',
                'description' => 'Warm Belgian molten chocolate lava cakes, New York baked cheesecakes, and classic Italian tiramisu.',
                'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80',
                'sort_order' => 6,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Beverages & Mocktails',
                'slug' => 'beverages-mocktails',
                'description' => 'Fresh mint margaritas, sparkling berry mojitos, blue lagoon fizz, and artisanal iced Spanish lattes.',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                'sort_order' => 7,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Soups & Fresh Salads',
                'slug' => 'soups-salads',
                'description' => 'Velvety wild mushroom soup, classic Caesar salad with parmesan shavings, and Mediterranean quinoa bowls.',
                'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800&q=80',
                'sort_order' => 8,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }

        Category::query()->whereNotIn('slug', collect($categories)->pluck('slug'))->delete();
    }
}
