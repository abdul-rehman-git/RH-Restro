<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('slug');

        $products = [
            [
                'category_slug' => 'signature-mains',
                'title' => 'Prime Angus Ribeye Steak (400g)',
                'slug' => 'prime-angus-ribeye-steak',
                'sku' => 'RST-STK-001',
                'short_description' => 'Charbroiled 400g grass-fed Black Angus ribeye served with roasted garlic herb butter, grilled asparagus, and truffle potato mash.',
                'description' => 'Our Prime Angus Ribeye is aged for 28 days for exceptional tenderness and marbling. Seared over high-flame volcanic rock grill to lock in natural juices, it is finished with a crown of rosemary-infused butter and served with seasonal garden vegetables and velvety truffle potato mash.',
                'dimensions' => 'Portion: 400g (Serves 1-2)',
                'materials' => '100% Certified Black Angus Beef, French Butter, Fresh Rosemary, Garlic, Truffle Mash, Asparagus',
                'feature_points' => [
                    '28-day dry-aged prime beef cut',
                    'High-heat flame-seared for intense crust and juicy center',
                    'Served with creamy house-made truffle mash and grilled greens',
                    'Complimentary peppercorn or mushroom jus sauce',
                ],
                'care_instructions' => 'Best enjoyed immediately hot. Reheat in oven at 180°C for 3-4 minutes if needed.',
                'shipping_note' => 'Delivered in insulated heat-locking containers to preserve crust and warmth.',
                'price' => 3450.00,
                'compare_price' => 3950.00,
                'badge_label' => "Chef's Pick",
                'stock_quantity' => 25,
                'sort_order' => 1,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                    'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80',
                    'https://images.unsplash.com/photo-1504973960431-1c467e159aa4?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Medium Rare', 'sku' => 'RST-STK-001-MR', 'price' => 3450.00, 'stock_quantity' => 10],
                    ['name' => 'Medium', 'sku' => 'RST-STK-001-MED', 'price' => 3450.00, 'stock_quantity' => 10],
                    ['name' => 'Well Done', 'sku' => 'RST-STK-001-WD', 'price' => 3450.00, 'stock_quantity' => 5],
                ],
            ],
            [
                'category_slug' => 'gourmet-burgers',
                'title' => 'Double Truffle Smashed Burger',
                'slug' => 'double-truffle-smashed-burger',
                'sku' => 'RST-BGR-001',
                'short_description' => 'Two crispy-edged smashed Angus patties, double melted Wisconsin cheddar, caramelized balsamic onions, and black truffle aioli on toasted brioche.',
                'description' => 'Our flagship burger starts with a custom blend of fresh chuck, brisket, and short rib. Smashed paper-thin on a screaming-hot cast-iron plancha to create lace-crisp edges, blanketed in double vintage cheddar, caramelized onions, and our signature black truffle mayonnaise on butter-toasted brioche.',
                'dimensions' => 'Includes seasoned skin-on fries',
                'materials' => 'Prime Angus Beef Blend, Aged Cheddar, Caramelized Onions, Truffle Aioli, Brioche Bun',
                'feature_points' => [
                    'Double 100% fresh Angus smash patties',
                    'Infused with aromatic black truffle mayonnaise',
                    'Served on golden French-style buttered brioche',
                    'Includes a side of crispy seasoned hand-cut fries',
                ],
                'care_instructions' => 'Eat fresh upon arrival. Reheat fries in an air fryer at 190°C for 2 minutes for ultimate crispiness.',
                'shipping_note' => 'Packaged with ventilated burger wrap to keep the bun soft and prevent sogginess.',
                'price' => 1250.00,
                'compare_price' => 1450.00,
                'badge_label' => 'Best Seller',
                'stock_quantity' => 50,
                'sort_order' => 2,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                    'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=800&q=80',
                    'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Single Patty', 'sku' => 'RST-BGR-001-SGL', 'price' => 950.00, 'stock_quantity' => 25],
                    ['name' => 'Double Patty (Original)', 'sku' => 'RST-BGR-001-DBL', 'price' => 1250.00, 'stock_quantity' => 25],
                ],
            ],
            [
                'category_slug' => 'pizzas-pastas',
                'title' => 'Wood-Fired Neapolitan Margherita Pizza',
                'slug' => 'wood-fired-margherita-pizza',
                'sku' => 'RST-PIZ-001',
                'short_description' => '48-hour fermented sourdough crust baked in a 900°F stone oven, topped with San Marzano tomatoes, fresh buffalo mozzarella, and fragrant garden basil.',
                'description' => 'A masterclass in authentic Italian pizza making. We ferment our Italian Tipo 00 flour dough for 48 hours for a light, digestible crust with iconic leopard-spotted char. Layered with crushed sweet San Marzano DOP tomatoes, imported buffalo mozzarella, fresh basil leaves, and cold-pressed extra virgin olive oil.',
                'dimensions' => 'Sizes: 10-inch or 14-inch',
                'materials' => 'Italian Tipo 00 Flour, San Marzano Tomatoes, Buffalo Mozzarella, Fresh Basil, Extra Virgin Olive Oil',
                'feature_points' => [
                    'Stone-baked in traditional 900°F wood-burning oven in 90 seconds',
                    '48-hour naturally leavened sourdough base',
                    '100% imported Italian buffalo mozzarella',
                    'Vegetarian friendly & halal',
                ],
                'care_instructions' => 'Reheat slices in a dry skillet over medium heat for 2 minutes for a crispy crust.',
                'shipping_note' => 'Delivered in perforated pizza boxes that preserve crust crispiness.',
                'price' => 1650.00,
                'compare_price' => 1950.00,
                'badge_label' => 'Authentic Italian',
                'stock_quantity' => 40,
                'sort_order' => 3,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                    'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&q=80',
                    'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=800&q=80',
                ],
                'variants' => [
                    ['name' => '10-Inch Regular', 'sku' => 'RST-PIZ-001-10', 'price' => 1350.00, 'stock_quantity' => 20],
                    ['name' => '14-Inch Large (Original)', 'sku' => 'RST-PIZ-001-14', 'price' => 1650.00, 'stock_quantity' => 20],
                ],
            ],
            [
                'category_slug' => 'starters-appetizers',
                'title' => 'Crispy Buttermilk Buffalo Wings (12 Pcs)',
                'slug' => 'crispy-buttermilk-buffalo-wings',
                'sku' => 'RST-APP-001',
                'short_description' => 'Double-fried buttermilk soaked chicken wings coated in house buttery hot cayenne glaze, served with crunchy celery and blue cheese dip.',
                'description' => 'Tender chicken wings soaked in herb buttermilk for 24 hours, dredged in seasoned cornmeal flour, and twice-fried to ultra-crispy perfection. Tossed in our signature tangy cayenne buffalo glaze and accompanied by cool celery sticks and artisan creamy dipping sauce.',
                'dimensions' => '12 Jumbo Wings',
                'materials' => 'Fresh Halal Chicken Wings, Buttermilk, Cayenne Hot Sauce, Butter, Garlic, Celery Sticks',
                'feature_points' => [
                    'Twice-fried for unmatched crispiness that lasts',
                    'Tossed in hot tangy house buffalo butter glaze',
                    'Includes house blue cheese or ranch dip and celery sticks',
                    'Choice of heat level from mild to extreme',
                ],
                'care_instructions' => 'Serve hot. To crisp up later, bake in an oven or air fryer at 200°C for 4 minutes.',
                'shipping_note' => 'Delivered in ventilated thermal packs to maintain crisp exterior.',
                'price' => 1150.00,
                'compare_price' => 1350.00,
                'badge_label' => 'Popular Starter',
                'stock_quantity' => 45,
                'sort_order' => 4,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?w=800&q=80',
                    'https://images.unsplash.com/photo-1527477321005-4d45d724b8c4?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Mild Honey BBQ', 'sku' => 'RST-APP-001-MLD', 'price' => 1150.00, 'stock_quantity' => 15],
                    ['name' => 'Classic Hot Buffalo', 'sku' => 'RST-APP-001-HOT', 'price' => 1150.00, 'stock_quantity' => 20],
                    ['name' => 'Fiery Ghost Pepper', 'sku' => 'RST-APP-001-FST', 'price' => 1250.00, 'stock_quantity' => 10],
                ],
            ],
            [
                'category_slug' => 'pizzas-pastas',
                'title' => 'Creamy Fettuccine Alfredo with Grilled Chicken',
                'slug' => 'creamy-fettuccine-alfredo',
                'sku' => 'RST-PAS-001',
                'short_description' => 'Fresh egg fettuccine tossed in 24-month aged Parmigiano-Reggiano cream sauce, garlic butter, and topped with sliced charbroiled chicken breast.',
                'description' => 'Silky handcrafted fettuccine pasta simmered in a velvety reduction of sweet cream, browned garlic butter, and freshly grated Parmigiano-Reggiano. Garnished with golden marinated grilled chicken strips, cracked black peppercorns, and fresh Italian parsley.',
                'dimensions' => 'Generous single entree serving (450g)',
                'materials' => 'Fresh Egg Pasta, Parmigiano-Reggiano, Heavy Cream, Garlic, Olive Oil, Fresh Herbs, Grilled Chicken',
                'feature_points' => [
                    'Handmade fresh egg pasta rolled daily',
                    'Authentic Parmigiano-Reggiano aged 24 months',
                    'Topped with herb-grilled juicy chicken breast',
                    'Served with warm garlic crostini bread',
                ],
                'care_instructions' => 'Enjoy hot. Stir in a teaspoon of warm milk or water if reheating.',
                'shipping_note' => 'Sealed in heat-retaining round pasta bowl.',
                'price' => 1550.00,
                'compare_price' => 1800.00,
                'badge_label' => 'Comfort Classic',
                'stock_quantity' => 35,
                'sort_order' => 5,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=800&q=80',
                    'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Standard Recipe', 'sku' => 'RST-PAS-001-STD', 'price' => 1550.00, 'stock_quantity' => 20],
                    ['name' => 'With Sautéed Portobello Mushrooms', 'sku' => 'RST-PAS-001-MSH', 'price' => 1750.00, 'stock_quantity' => 15],
                ],
            ],
            [
                'category_slug' => 'signature-mains',
                'title' => 'Pan-Seared Norwegian Salmon Fillet',
                'slug' => 'pan-seared-norwegian-salmon',
                'sku' => 'RST-SEA-001',
                'short_description' => 'Crispy-skinned fresh Norwegian salmon fillet resting on creamy lemon butter caper sauce with grilled baby potatoes and broccolini.',
                'description' => 'Sourced sustainably from cold Norwegian fjords, this premium salmon fillet is seared skin-down for a delightful crunch while keeping the delicate pink flesh succulent and flaky. Accompained by a delicate white wine lemon emulsion, capers, roasted baby potatoes, and charred broccolini.',
                'dimensions' => '250g Salmon Fillet + Sides',
                'materials' => 'Norwegian Salmon, Lemon, Capers, Butter, Baby Potatoes, Broccolini, Sea Salt',
                'feature_points' => [
                    'Sustainably sourced cold-water Norwegian salmon',
                    'Crisp skin with moist, flaky interior',
                    'Bright lemon caper reduction sauce',
                    'Nutritious, high in Omega-3, and gluten-free friendly',
                ],
                'care_instructions' => 'Best served fresh. Warm gently in an oven rather than microwave to maintain tender texture.',
                'shipping_note' => 'Specially insulated to preserve delicate salmon temperature.',
                'price' => 2850.00,
                'compare_price' => 3350.00,
                'badge_label' => 'Healthy Gourmet',
                'stock_quantity' => 20,
                'sort_order' => 6,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80',
                    'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80',
                ],
                'variants' => [],
            ],
            [
                'category_slug' => 'sizzlers-grills',
                'title' => 'Sizzling Smoked BBQ Platter',
                'slug' => 'sizzling-smoked-bbq-platter',
                'sku' => 'RST-SIZ-001',
                'short_description' => 'A grand sizzling feast of hickory-smoked lamb chops, skewered beef kofta, grilled chicken tikka, grilled corn, and spicy garlic naan.',
                'description' => 'Served on a crackling cast-iron sizzler bed of caramelized onions and bell peppers. Features a generous assortment of slow-smoked tender lamb cutlets, spiced beef skewers, succulent chicken tikka chunks, charred sweet corn cob, and warm fluffy garlic naan with minted yogurt sauce.',
                'dimensions' => 'Feast Platter (Serves 2-3 persons)',
                'materials' => 'Halal Lamb Chops, Ground Beef Skewers, Chicken Breast, Naan Bread, Yogurt, Mint, Bell Peppers',
                'feature_points' => [
                    'Authentic charcoal and hickory wood smoking',
                    'Loaded combination of lamb, beef, and chicken',
                    'Served on a sizzling hot cast-iron platter',
                    'Includes garlic naan, mint dip, and pickled onions',
                ],
                'care_instructions' => 'Consume piping hot.',
                'shipping_note' => 'Delivered with separated sauces and wrapped hot naans.',
                'price' => 3650.00,
                'compare_price' => 4200.00,
                'badge_label' => 'Feast Platter',
                'stock_quantity' => 20,
                'sort_order' => 7,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80',
                    'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Regular Platter (2 Persons)', 'sku' => 'RST-SIZ-001-REG', 'price' => 3650.00, 'stock_quantity' => 10],
                    ['name' => 'Jumbo Platter (4 Persons)', 'sku' => 'RST-SIZ-001-JMB', 'price' => 5850.00, 'stock_quantity' => 10],
                ],
            ],
            [
                'category_slug' => 'starters-appetizers',
                'title' => 'Loaded Truffle & Parmesan Fries',
                'slug' => 'loaded-truffle-parmesan-fries',
                'sku' => 'RST-APP-002',
                'short_description' => 'Golden hand-cut fries tossed in white truffle oil, finely grated aged Parmesan, sea salt crystals, and freshly chopped chives with garlic dip.',
                'description' => 'Crisp on the outside, fluffy on the inside. Our Idaho potatoes are double-cooked in peanut oil, tossed while piping hot in aromatic Italian white truffle oil, dusted with freshly microplaned aged Parmesan cheese, fresh chives, and served alongside house roasted garlic emulsion.',
                'dimensions' => 'Shareable bowl (300g)',
                'materials' => 'Idaho Potatoes, White Truffle Oil, Parmigiano-Reggiano, Sea Salt, Chives, Garlic Mayo',
                'feature_points' => [
                    'Double-cooked fresh Idaho potatoes',
                    'Drizzled with genuine Italian white truffle oil',
                    'Heavily dusted with authentic aged parmesan',
                    'Comes with house garlic aioli dip',
                ],
                'care_instructions' => 'Best eaten immediately. Reheat in air fryer for 90 seconds.',
                'shipping_note' => 'Delivered in steam-releasing ventilated carton.',
                'price' => 750.00,
                'compare_price' => 900.00,
                'badge_label' => 'Crowd Favorite',
                'stock_quantity' => 60,
                'sort_order' => 8,
                'is_featured' => false,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80',
                ],
                'variants' => [],
            ],
            [
                'category_slug' => 'desserts-sweets',
                'title' => 'Warm Belgian Molten Lava Cake',
                'slug' => 'belgian-molten-lava-cake',
                'sku' => 'RST-DES-001',
                'short_description' => 'Decadent 70% dark Belgian chocolate sponge with a warm flowing liquid chocolate center, accompanied by Madagascar vanilla bean gelato.',
                'description' => 'An absolute chocolate lover paradise. Crafted with 70% Callebaut Belgian dark chocolate and rich butter, this baked cake breaks open to release a luscious fountain of warm chocolate ganache. Dusted with powdered sugar and paired with chilled artisanal vanilla bean gelato.',
                'dimensions' => 'Single individual cake + Gelato scoop',
                'materials' => 'Callebaut 70% Belgian Dark Chocolate, Organic Eggs, French Butter, Madagascar Vanilla Gelato',
                'feature_points' => [
                    'Made with 70% premium Belgian chocolate',
                    'Gooey flowing warm molten center',
                    'Paired with Madagascar vanilla gelato',
                    'Freshly baked to order',
                ],
                'care_instructions' => 'Microwave for 15-20 seconds to activate the molten lava center before serving.',
                'shipping_note' => 'Gelato is packaged separately in insulated cold cup with dry packaging.',
                'price' => 750.00,
                'compare_price' => 900.00,
                'badge_label' => 'Sweet Indulgence',
                'stock_quantity' => 30,
                'sort_order' => 9,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'With Vanilla Gelato', 'sku' => 'RST-DES-001-VAN', 'price' => 750.00, 'stock_quantity' => 15],
                    ['name' => 'With Salted Caramel Gelato', 'sku' => 'RST-DES-001-CAR', 'price' => 850.00, 'stock_quantity' => 15],
                ],
            ],
            [
                'category_slug' => 'beverages-mocktails',
                'title' => 'Signature Fresh Mint Lemonade & Mojito',
                'slug' => 'signature-mint-lemonade-mojito',
                'sku' => 'RST-BEV-001',
                'short_description' => 'Crisp refreshing blend of hand-crushed fresh mint leaves, zesty lemon juice, cane sugar, and sparkling soda over crushed ice.',
                'description' => 'The ultimate thirst quencher. Hand-crushed Moroccan spearmint muddled with freshly squeezed Meyer lemon juice, pure organic cane syrup, and topped with chilled sparkling mineral water over mountain ice crystals.',
                'dimensions' => '16 oz (480ml) Cup',
                'materials' => 'Fresh Spearmint, Fresh Lemon Juice, Organic Cane Sugar, Sparkling Water, Ice',
                'feature_points' => [
                    'Made with 100% fresh garden mint and lemons',
                    'No artificial syrups or food colorings',
                    'Crisp, refreshing, and digestive',
                    'Served over crystal-clear crushed ice',
                ],
                'care_instructions' => 'Drink chilled. Shake or stir gently before sipping.',
                'shipping_note' => 'Delivered in tightly sealed spill-proof beverage cups.',
                'price' => 450.00,
                'compare_price' => 550.00,
                'badge_label' => 'Cool Refresher',
                'stock_quantity' => 80,
                'sort_order' => 10,
                'is_featured' => false,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                ],
                'variants' => [
                    ['name' => 'Fresh Mint Lemonade', 'sku' => 'RST-BEV-001-MNT', 'price' => 450.00, 'stock_quantity' => 40],
                    ['name' => 'Sparkling Berry Mojito', 'sku' => 'RST-BEV-001-BRY', 'price' => 550.00, 'stock_quantity' => 40],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $category = $categories->get($productData['category_slug']);
            if (! $category) {
                continue;
            }

            $variants = $productData['variants'] ?? [];
            unset($productData['category_slug'], $productData['variants']);

            $productData['category_id'] = $category->id;

            /** @var Product $product */
            $product = Product::updateOrCreate(
                ['slug' => $productData['slug']],
                $productData,
            );

            // Re-sync variants
            $product->variants()->delete();
            foreach ($variants as $index => $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variantData['name'],
                    'sku' => $variantData['sku'] ?? "{$product->sku}-{$index}",
                    'price' => $variantData['price'],
                    'stock_quantity' => $variantData['stock_quantity'] ?? 10,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }

        Product::query()->whereNotIn('slug', collect($products)->pluck('slug'))->delete();
    }
}
