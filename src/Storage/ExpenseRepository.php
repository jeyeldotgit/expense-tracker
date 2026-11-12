<?php

declare(strict_types=1);

namespace ExpenseTracker\Storage;

use ExpenseTracker\Domain\Category;
use ExpenseTracker\Domain\Expense;
use RuntimeException;

final class ExpenseRepository
{
    public function __construct(private readonly string $filePath)
    {
    }

    public function add(Expense $expense): void
    {
        $expenses = $this->all();
        $expenses[] = $expense;
        $this->save($expenses);
    }

    /** @return list<Expense> */
    public function all(?Category $category = null): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $contents = file_get_contents($this->filePath);
        if ($contents === false) {
            throw new RuntimeException('Could not read the expenses file. Check its permissions.');
        }
        if (trim($contents) === '') {
            return [];
        }

        try {
            $rows = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('The expenses file contains invalid JSON. Restore or remove the file to continue.', 0, $exception);
        }

        if (!is_array($rows)) {
            throw new RuntimeException('The expenses file has an invalid format.');
        }

        $expenses = array_map(static fn (array $row): Expense => Expense::fromArray($row), $rows);
        if ($category !== null) {
            $expenses = array_values(array_filter(
                $expenses,
                static fn (Expense $expense): bool => $expense->category === $category,
            ));
        }

        return $expenses;
    }

    /** @param list<Expense> $expenses */
    private function save(array $expenses): void
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Could not create data directory: %s', $directory));
        }

        $rows = array_map(static fn (Expense $expense): array => $expense->toArray(), $expenses);
        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (file_put_contents($this->filePath, $json . PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('Could not save expenses. Check that the data file is writable.');
        }
    }
}
