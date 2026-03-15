<?php

require_once __DIR__ . '/common.php';

/**
 * Runs a list of database scripts in order.
 *
 * This script is intended as a one-shot "setup" helper for CI / docker / dev
 * environments. It runs the existing database setup scripts and reports their
 * status.
 */

declare(strict_types=1);

$scripts = [
    'migrate.php',
    'seed_teams_simple.php',
    'init_belt.php',
    'seed_games_simple.php',
];

$baseDir = __DIR__;
$logLines = [];

$logLines[] = "Database setup started: " . date('c');
$logLines[] = "Database path: " . db_get_path();

foreach ($scripts as $script) {
    $logLines[] = "\n-- Running {$script} --";

    $cmd = sprintf(
        'php %s 2>&1',
        escapeshellarg($baseDir . '/' . $script)
    );

    $output = [];
    $exitCode = 0;

    exec($cmd, $output, $exitCode);

    $logLines[] = implode("\n", $output);

    if ($exitCode !== 0) {
        $logLines[] = "{$script} exited with code {$exitCode}.";
        $logLines[] = "Aborting remaining steps.";
        break;
    }

    $logLines[] = "{$script} completed successfully.";
}

$logLines[] = "\nDatabase setup finished: " . date('c');

$log = implode("\n", $logLines) . "\n";
echo $log;
