<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchByColumns
{
    /**
     * @param  array<int, string>  $columns
     */
    public function __construct(
        protected Request $request,
        protected array $columns,
        protected string $queryParam = 'search',
    ) {
    }

    public function handle(Builder $query, Closure $next): Builder
    {
        $search = trim((string) $this->request->string($this->queryParam));

        if ($search !== '' && $this->columns !== []) {
            $query->where(function (Builder $builder) use ($search) {
                foreach ($this->columns as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return $next($query);
    }
}
