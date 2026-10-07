<?php

declare(strict_types=1);

namespace App\DTOs;

class ImportResultDTO
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public int $productsImported = 0,
        public int $rowsFailed = 0,
        public int $missingImages = 0,
        public int $skippedRows = 0,
        public array $errors = [],
    ) {}

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function incrementProductsImported(): void
    {
        $this->productsImported++;
    }

    public function incrementRowsFailed(): void
    {
        $this->rowsFailed++;
    }

    public function incrementMissingImages(): void
    {
        $this->missingImages++;
    }

    public function incrementSkippedRows(): void
    {
        $this->skippedRows++;
    }
}
