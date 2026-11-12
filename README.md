# Expense Tracker CLI

A small, framework-free PHP command-line application for recording and reviewing student-organization expenses. Entries are stored in a local JSON file, so they remain available the next time the program runs.

## Requirements

- PHP 8.3 or newer
- Composer 2

## Setup

```bash
composer install
```

## Usage

```bash
# Add an expense: amount, category, date, optional description
php expense.php add 250.00 food 2026-11-12 "Snacks for the meeting"
php expense.php add 120.50 transport 2026-11-12 "Jeepney fare"

# List every expense, or filter by category
php expense.php list
php expense.php list food

# Show all expenses total, or total for one category
php expense.php total
php expense.php total food

# Show command help
php expense.php help
```

Supported categories: `food`, `transport`, `supplies`, `events`, and `other`.

Amounts must be positive and may have up to two decimal places. Dates must be real calendar dates in `YYYY-MM-DD` format. Invalid values print a specific error and return a non-zero exit code; they do not add an entry.

## Data storage

By default, expenses are saved in `data/expenses.json` relative to the project directory. The file is created automatically when the first expense is added and is intentionally excluded from Git so personal financial records are not committed.

To use another file, set `EXPENSES_FILE`:

```bash
EXPENSES_FILE=/path/to/my-expenses.json php expense.php list
```

## Tests

```bash
composer test
```

The PHPUnit suite covers expense creation, exact cent parsing, persistence across repository instances, category filtering and totals, and rejection of negative/zero amounts, unknown categories, and malformed dates.

## Continuous integration

GitHub Actions runs `composer install` and the PHPUnit suite on every push and pull request using PHP 8.3. See `.github/workflows/ci.yml`.
