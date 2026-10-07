<?php

declare(strict_types=1);

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @var Collection<int, Collection<string, mixed>>
     */
    protected Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        $this->rows = $this->rows->concat($rows);
    }

    /**
     * @return Collection<int, Collection<string, mixed>>
     */
    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
