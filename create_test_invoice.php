<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$dbfile = __DIR__ . '/data/mitas_system.sqlite';
require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$invoiceNumber = 'AUTO-' . time();
$pdo->exec("CREATE TABLE IF NOT EXISTS invoice_items (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	invoice_id INTEGER NOT NULL,
	item TEXT NOT NULL,
	category TEXT NOT NULL DEFAULT '',
	quantity INTEGER NOT NULL DEFAULT 1,
	unit_price REAL NOT NULL DEFAULT 0,
	discount_percent REAL NOT NULL DEFAULT 0,
	amount REAL NOT NULL DEFAULT 0,
	product_id INTEGER,
	created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);");

$pdo->exec("INSERT INTO invoices (invoice_number, customer_name, item, total_amount, notes, status) VALUES ('".addslashes($invoiceNumber)."', 'Automated Test Customer', 'Multiple items', 35000, 'Auto-generated for test', 'Pending')");
$invoiceId = (int)$pdo->lastInsertId();
$stmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, item, category, quantity, unit_price, discount_percent, amount, product_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$invoiceId, 'Router Model X', 'Networking', 2, 12000, 0, 24000, null]);
$stmt->execute([$invoiceId, 'SSD 1TB', 'Storage', 1, 11000, 0, 11000, null]);
echo $invoiceId;
