<?php

declare(strict_types=1);

namespace ExpenseTracker\Tests;

use ExpenseTracker\Domain\Category;
use ExpenseTracker\Domain\Expense;
use PHPUnit\Framework\TestCase;

final class ExpenseTest extends TestCase
{
    public function testExpenseCanBeCreatedAndCompared(): void
    {
        $expense = new Expense('expense-1', 12550, Category::FOOD, Expense::parseDate('2026-11-12'), 'Snacks');

        self::assertSame('expense-1', $expense->id);
        self::assertSame(12550, $expense->amountCents);
        self::assertSame(Category::FOOD, $expense->category);
        self::assertSame('2026-11-12', $expense->date->format('Y-m-d'));
        self::assertSame('Snacks', $expense->description);
    }

    public function testAmountIsStoredAsExactCents(): void
    {
        self::assertSame(12345, Expense::parseAmount('123.45'));
        self::assertSame(100, Expense::parseAmount('1'));
    }

    public function testNegativeAmountIsRejectedWithSpecificMessage(): void
    {
        $this->expectExceptionMessage('Negative amounts are not allowed');
        Expense::parseAmount('-25.00');
    }

    public function testZeroAmountIsRejected(): void
    {
        $this->expectExceptionMessage('Amount must be greater than zero');
        Expense::parseAmount('0.00');
    }

    public function testUnknownCategoryIsRejectedWithSpecificMessage(): void
    {
        $this->expectExceptionMessage('Unknown category "travel"');
        Category::fromInput('travel');
    }

    public function testMalformedDateIsRejectedWithSpecificMessage(): void
    {
        $this->expectExceptionMessage('Date "2026-02-30" is invalid');
        Expense::parseDate('2026-02-30');
    }
}
