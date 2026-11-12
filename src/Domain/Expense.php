<?php

declare(strict_types=1);

namespace ExpenseTracker\Domain;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Expense
{
    public function __construct(
        public string $id,
        public int $amountCents,
        public Category $category,
        public DateTimeImmutable $date,
        public string $description = '',
    ) {
        if ($amountCents <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }
        if (trim($id) === '') {
            throw new InvalidArgumentException('Expense ID cannot be empty.');
        }
    }

    public static function parseAmount(string $amount): int
    {
        $amount = trim($amount);
        if (str_starts_with($amount, '-')) {
            throw new InvalidArgumentException('Negative amounts are not allowed. Enter an amount greater than zero.');
        }
        if (!preg_match('/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Amount must be a positive number with up to 2 decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        if ($cents <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        return $cents;
    }

    public static function parseDate(string $date): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsed->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException(sprintf('Date "%s" is invalid. Use YYYY-MM-DD (for example, 2026-11-12).', $date));
        }

        return $parsed;
    }

    /** @return array{id: string, amount_cents: int, category: string, date: string, description: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount_cents' => $this->amountCents,
            'category' => $this->category->value,
            'date' => $this->date->format('Y-m-d'),
            'description' => $this->description,
        ];
    }

    /** @param array{id: string, amount_cents: int, category: string, date: string, description?: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['amount_cents'],
            Category::fromInput($data['category']),
            self::parseDate($data['date']),
            $data['description'] ?? '',
        );
    }
}
