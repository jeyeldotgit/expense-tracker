#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ExpenseTracker\Console\ExpenseApplication;
use ExpenseTracker\Storage\ExpenseRepository;

$dataFile = getenv('EXPENSES_FILE') ?: __DIR__ . '/data/expenses.json';
$app = new ExpenseApplication(new ExpenseRepository($dataFile));
exit($app->run(array_slice($argv, 1)));
