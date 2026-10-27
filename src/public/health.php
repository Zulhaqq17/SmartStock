<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = db();
    echo "SmartStock is running.\n";
    echo "Database connection: OK\n\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables (" . count($tables) . "): " . implode(', ', $tables) . "\n\n";

    // FEFO sanity check: earliest-expiring batches first
    $rows = $pdo->query(
        "SELECT p.product_name, b.batch_number, b.current_quantity, b.expiry_date
         FROM batches b JOIN products p ON p.product_id = b.product_id
         WHERE b.current_quantity > 0
         ORDER BY b.expiry_date ASC"
    )->fetchAll();

    echo "Active batches (FEFO order):\n";
    foreach ($rows as $r) {
        echo "- {$r['expiry_date']} | {$r['product_name']} | {$r['batch_number']} | qty {$r['current_quantity']}\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database connection FAILED: " . $e->getMessage();
}
