<?php
/**
 * Quick integrity & DB connection test
 * Run:  php test_db.php
 */
declare(strict_types=1);

const REQUIRED_PATHS = [
    'core/bootstrap.php',
    'core/Database.php',
    'core/Router.php',
    '.env',
    'vendor/autoload.php',
];

function abort(string $msg): void
{
    echo "❌ $msg" . PHP_EOL;
    exit(1);
}

/* 1 ─ Check filesystem */
foreach (REQUIRED_PATHS as $path) {
    if (!file_exists($path)) {
        abort("Missing file: $path");
    }
}

/* 2 ─ Load env & bootstrap */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/Database.php';

/* 3 ─ Test DB connection */
try {
    $pdo = Database::conn();          // ya no lleva \Database porque lo incluimos arriba
    echo "✅ PDO connection OK to {$_ENV['DB_NAME']} on {$_ENV['DB_HOST']}" . PHP_EOL;
} catch (Throwable $e) {
    abort("DB connection failed: " . $e->getMessage());
}

/* 4 ─ Check expected views */
$expectedViews = ['v_products', 'v_orders', 'v_users'];
$rows = $pdo->query("SELECT table_name FROM information_schema.views 
                     WHERE table_schema = '{$_ENV['DB_NAME']}' 
                       AND table_name IN ('" . implode("','", $expectedViews) . "')")
            ->fetchAll(PDO::FETCH_COLUMN);

foreach ($expectedViews as $view) {
    if (!in_array($view, $rows, true)) {
        abort("Missing view: $view");
    }
}

echo "✅ All expected views exist." . PHP_EOL;
echo "🎯 Phase 1-3 integrity test passed — ready for Phase 4." . PHP_EOL;