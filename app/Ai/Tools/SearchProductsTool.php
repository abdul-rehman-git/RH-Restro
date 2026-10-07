<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SearchProductsTool implements Tool
{
    public function description(): string
    {
        return 'Search for available products in the store by keyword to help customers find what they are looking for. Returns a list of products with their title, price, and the exact URL link to the product page.';
    }

    public function handle(Request $request): string
    {
        $query = $request->arguments['query'] ?? '';
        
        $products = Product::query()
            ->active()
            ->search($query)
            ->take(5)
            ->get();
            
        if ($products->isEmpty()) {
            return 'No active products found matching the search query: ' . $query;
        }
        
        return $products->map(function ($product) {
            $price = number_format((float) $product->price, 2);
            $url = "/product/{$product->slug}";
            $imageUrl = $product->imageUrl();
            
            $html = '<a href="' . $url . '" class="flex items-center gap-3.5 p-3 my-2.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-amber-600 dark:hover:border-amber-500 transition-all shadow-md group block decoration-transparent cursor-pointer">';
            if ($imageUrl) {
                $html .= '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product->title) . '" class="w-14 h-14 sm:w-16 sm:h-16 object-cover rounded-lg shadow-sm shrink-0 group-hover:scale-105 transition-transform duration-300" />';
            }
            $html .= '<div class="flex-1 min-w-0">';
            $html .= '<span class="text-amber-600 dark:text-amber-500 group-hover:brightness-110 font-semibold text-sm block truncate group-hover:underline">' . htmlspecialchars($product->title) . '</span>';
            $html .= '<p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5 truncate">' . htmlspecialchars($product->short_description ?? '') . '</p>';
            $html .= '<p class="text-sm text-gray-900 dark:text-gray-100 font-bold mt-1">Rs. ' . $price . '</p>';
            $html .= '</div>';
            $html .= '</a>';
            
            return $html;
        })->implode("");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string('The search term, product category, or keyword provided by the customer.')->required(),
        ];
    }
}
