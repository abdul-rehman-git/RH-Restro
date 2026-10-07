<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FilterByColumns
{
    /**
     * @param  array<int, array{
     *     query:string,
     *     column:string,
     *     cast?:string,
     *     operator?:string,
     *     values?:array<string, mixed>
     * }>  $filters
     */
    public function __construct(
        protected Request $request,
        protected array $filters,
    ) {
    }

    public function handle(Builder $query, Closure $next): Builder
    {
        foreach ($this->filters as $filter) {
            $rawValue = $this->request->input($filter['query']);

            if ($rawValue === null || $rawValue === '') {
                continue;
            }

            if (isset($filter['values'])) {
                if (! array_key_exists((string) $rawValue, $filter['values'])) {
                    continue;
                }

                $value = $filter['values'][(string) $rawValue];
            } else {
                $value = $this->castValue($rawValue, $filter['cast'] ?? 'string');

                if (($filter['cast'] ?? null) === 'int' && $value <= 0) {
                    continue;
                }
            }

            $query->where(
                $filter['column'],
                $filter['operator'] ?? '=',
                $value,
            );
        }

        return $next($query);
    }

    protected function castValue(mixed $value, string $cast): mixed
    {
        return match ($cast) {
            'int' => (int) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            default => (string) $value,
        };
    }
}
