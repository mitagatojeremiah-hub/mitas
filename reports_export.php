<?php
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');
$out = fopen('php://output', 'w');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="mitas_report_'. $start .'_'. $end .'.csv"');

fputcsv($out, ['Type','Reference','Amount','Date']);
$stmt = $pdo->query("SELECT 'invoice' as type, invoice_number as ref, total_amount as amt, created_at as d FROM invoices WHERE date(created_at) BETWEEN '". $start ."' AND '". $end ."'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($out, [$r['type'],$r['ref'],$r['amt'],$r['d']]);
$stmt = $pdo->query("SELECT 'purchase' as type, id as ref, amount as amt, created_at as d FROM purchases WHERE date(created_at) BETWEEN '". $start ."' AND '". $end ."'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($out, [$r['type'],$r['ref'],$r['amt'],$r['d']]);
$stmt = $pdo->query("SELECT 'payment' as type, id as ref, amount as amt, created_at as d FROM payments WHERE date(created_at) BETWEEN '". $start ."' AND '". $end ."'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($out, [$r['type'],$r['ref'],$r['amt'],$r['d']]);
fclose($out);
