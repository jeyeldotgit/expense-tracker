<?php

declare(strict_types=1);

namespace ExpenseTracker\Tests;

use ExpenseTracker\Domain\Category;
use ExpenseTracker\Domain\Expense;
use ExpenseTracker\Storage\ExpenseRepository;
use PHPUnit\Framework\TestCase;

final class ExpenseRepositoryTest extends TestCase
{
    private string $directory;
    private string $file;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/expense-tracker-' . bin2hex(random_bytes(5));
        $this->file = $this->directory . '/expenses.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    public function testExpensesPersistAcrossRepositoryInstances(): void
    {
        $firstRun = new ExpenseRepository($this->file);
        $firstRun->add(new Expense('a1', 2500, Category::SUPPLIES, Expense::parseDate('2026-11-10'), 'Markers'));

        $nextRun = new ExpenseRepository($this->file);
        $loaded = $nextRun->all();

        self::assertCount(1, $loaded);
        self::assertSame(2500, $loaded[0]->amountCents);
        self::assertSame('Markers', $loaded[0]->description);
    }

    public function testExpensesCanBeFilteredByCategory(): void
    {
        $repository = new ExpenseRepository($this->file);
        $repository->add(new Expense('a1', 2500, Category::SUPPLIES, Expense::parseDate('2026-11-10')));
        $repository->add(new Expense('a2', 5000, Category::FOOD, Expense::parseDate('2026-11-11')));

        $filtered = $repository->all(Category::FOOD);

        self::assertCount(1, $filtered);
        self::assertSame(Category::FOOD, $filtered[0]->category);
    }

    public function testCategoryTotalUsesOnlyMatchingExpenses(): void
    {
        $repository = new ExpenseRepository($this->file);
        $repository->add(new Expense('a1', 2500, Category::FOOD, Expense::parseDate('2026-11-10')));
        $repository->add(new Expense('a2', 5000, Category::FOOD, Expense::parseDate('2026-11-11')));
        $repository->add(new Expense('a3', 9000, Category::TRANSPORT, Expense::parseDate('2026-11-12')));

        $foodTotal = array_sum(array_map(static fn (Expense $expense): int => $expense->amountCents, $repository->all(Category::FOOD)));

        self::assertSame(7500, $foodTotal);
    }
}
