<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SortByColumn
{
    /**
     * @param  array<int, string>  $allowedColumns
     * @param  array<int, array{0:string,1:string}>  $defaultSorts
     */
    public function __construct(
        protected Request $request,
        protected array $allowedColumns,
        protected array $defaultSorts = [],
        protected string $columnParam = 'sort_by',
        protected string $directionParam = 'sort_direction',
    ) {
    }

    public function handle(Builder $query, Closure $next): Builder
    {
        $column = $this->request->string($this->columnParam)->toString();
        $direction = strtolower($this->request->string($this->directionParam)->toString());

        if (in_array($column, $this->allowedColumns, true)) {
            $query->orderBy($column, $direction === 'desc' ? 'desc' : 'asc');

            return $next($query);
        }

        foreach ($this->defaultSorts as [$defaultColumn, $defaultDirection]) {
            $query->orderBy($defaultColumn, $defaultDirection);
        }

        return $next($query);
    }
}
