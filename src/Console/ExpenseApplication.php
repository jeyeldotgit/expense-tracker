<?php

declare(strict_types=1);

namespace ExpenseTracker\Console;

use ExpenseTracker\Domain\Category;
use ExpenseTracker\Domain\Expense;
use ExpenseTracker\Storage\ExpenseRepository;
use InvalidArgumentException;
use Throwable;

final class ExpenseApplication
{
    public function __construct(private readonly ExpenseRepository $repository)
    {
    }

    /** @param list<string> $arguments */
    public function run(array $arguments): int
    {
        try {
            $command = $arguments[0] ?? 'help';
            return match ($command) {
                'add' => $this->add($arguments),
                'list' => $this->list($arguments),
                'total' => $this->total($arguments),
                'help', '--help', '-h' => $this->help(),
                default => throw new InvalidArgumentException(sprintf('Unknown command "%s". Run "php expense.php help" for usage.', $command)),
            };
        } catch (InvalidArgumentException $exception) {
            fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
            return 1;
        } catch (Throwable $exception) {
            fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
            return 1;
        }
    }

    /** @param list<string> $arguments */
    private function add(array $arguments): int
    {
        if (count($arguments) < 4 || count($arguments) > 5) {
            throw new InvalidArgumentException('Usage: php expense.php add <amount> <category> <YYYY-MM-DD> [description]');
        }

        $expense = new Expense(
            bin2hex(random_bytes(8)),
            Expense::parseAmount($arguments[1]),
            Category::fromInput($arguments[2]),
            Expense::parseDate($arguments[3]),
            $arguments[4] ?? '',
        );
        $this->repository->add($expense);
        printf("Added %s %s expense on %s.\n", $this->formatMoney($expense->amountCents), $expense->category->value, $expense->date->format('Y-m-d'));

        return 0;
    }

    /** @param list<string> $arguments */
    private function list(array $arguments): int
    {
        if (count($arguments) > 2) {
            throw new InvalidArgumentException('Usage: php expense.php list [category]');
        }
        $category = isset($arguments[1]) ? Category::fromInput($arguments[1]) : null;
        $expenses = $this->repository->all($category);
        if ($expenses === []) {
            echo $category === null ? "No expenses recorded yet.\n" : sprintf("No %s expenses recorded yet.\n", $category->value);
            return 0;
        }

        printf("%-12s %-12s %-12s %s\n", 'DATE', 'CATEGORY', 'AMOUNT', 'DESCRIPTION');
        foreach ($expenses as $expense) {
            printf("%-12s %-12s %-12s %s\n", $expense->date->format('Y-m-d'), $expense->category->value, $this->formatMoney($expense->amountCents), $expense->description);
        }

        return 0;
    }

    /** @param list<string> $arguments */
    private function total(array $arguments): int
    {
        if (count($arguments) > 2) {
            throw new InvalidArgumentException('Usage: php expense.php total [category]');
        }
        $category = isset($arguments[1]) ? Category::fromInput($arguments[1]) : null;
        $expenses = $this->repository->all($category);
        $total = array_sum(array_map(static fn (Expense $expense): int => $expense->amountCents, $expenses));
        printf("Total%s: %s (%d expenses)\n", $category === null ? '' : ' for ' . $category->value, $this->formatMoney($total), count($expenses));

        return 0;
    }

    private function help(): int
    {
        echo <<<HELP
Expense Tracker

Commands:
  php expense.php add <amount> <category> <YYYY-MM-DD> [description]
  php expense.php list [category]
  php expense.php total [category]
  php expense.php help

Categories: food, transport, supplies, events, other
Amounts use up to two decimal places. Dates use YYYY-MM-DD.

HELP;
        return 0;
    }

    private function formatMoney(int $cents): string
    {
        return 'PHP ' . number_format($cents / 100, 2, '.', ',');
    }
}
