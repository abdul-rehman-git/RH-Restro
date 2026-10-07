<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;

class GetCategoryOptionsAction
{
    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function handle(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }
}
