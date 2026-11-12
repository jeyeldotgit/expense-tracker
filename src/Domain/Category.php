<?php

declare(strict_types=1);

namespace ExpenseTracker\Domain;

use InvalidArgumentException;

enum Category: string
{
    case FOOD = 'food';
    case TRANSPORT = 'transport';
    case SUPPLIES = 'supplies';
    case EVENTS = 'events';
    case OTHER = 'other';

    public static function fromInput(string $value): self
    {
        return self::tryFrom(strtolower(trim($value)))
            ?? throw new InvalidArgumentException(
                sprintf('Unknown category "%s". Allowed categories: %s.', $value, implode(', ', self::values()))
            );
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $category): string => $category->value, self::cases());
    }
}
