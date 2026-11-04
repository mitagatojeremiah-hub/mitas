<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if ($argc < 2) { echo "Usage: php show_invoice_items.php <invoice_id>\n"; exit(1);} 
$invoiceId = (int)$argv[1];
require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
$stmt->execute([$invoiceId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
