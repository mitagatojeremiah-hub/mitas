<?php
session_start();
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$purchaseSum = (float)$pdo->query('SELECT COALESCE(SUM(amount), 0) FROM purchases')->fetchColumn();
$invoiceRevenue = (float)$pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM invoices')->fetchColumn();
$orderRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status IN ('Sold','Paid')")->fetchColumn();
$todayInvoiceRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE date(created_at) = date('now')")->fetchColumn();
$todayOrderRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status IN ('Sold','Paid') AND date(created_at) = date('now')")->fetchColumn();

// Compute profit/loss only after orders are fully paid using stored order profit
$totalProfit = (float)$pdo->query("SELECT COALESCE(SUM(profit),0) FROM orders WHERE status = 'Paid' AND profit >= 0")->fetchColumn();
$totalLoss = abs((float)$pdo->query("SELECT COALESCE(SUM(profit),0) FROM orders WHERE status = 'Paid' AND profit < 0")->fetchColumn());

$counts = [
    'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'customers' => (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
    'suppliers' => (int)$pdo->query('SELECT COUNT(*) FROM suppliers')->fetchColumn(),
    'invoices' => (int)$pdo->query('SELECT COUNT(*) FROM invoices')->fetchColumn(),
    'low_stock' => (int)$pdo->query('SELECT COUNT(*) FROM products WHERE quantity < 10')->fetchColumn(),
    'inventory_value' => (float)$pdo->query('SELECT COALESCE(SUM(quantity * buy_price), 0) FROM products')->fetchColumn(),
    'revenue' => $invoiceRevenue + $orderRevenue,
    'total_sell' => $invoiceRevenue + $orderRevenue,
    'total_profit' => $totalProfit,
    'total_loss' => $totalLoss,
    'order_revenue' => $orderRevenue,
    'net_profit' => ($invoiceRevenue + $orderRevenue) - $purchaseSum,
    'today_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE date(created_at) = date('now')")->fetchColumn(),
    'today_invoices' => (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE date(created_at) = date('now')")->fetchColumn(),
    'today_revenue' => $todayInvoiceRevenue + $todayOrderRevenue,
    'stock_graph' => $pdo->query('SELECT name, quantity FROM products ORDER BY quantity ASC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC),
];

// compute total outstanding for invoices and orders, excluding settled documents
$totalOutstanding = 0.0;
$invoiceOutstanding = (float)$pdo->query("SELECT COALESCE(SUM(CASE WHEN COALESCE(settled, 0) = 0 THEN COALESCE(final_total, total_amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = invoices.id), 0) ELSE 0 END), 0) FROM invoices")->fetchColumn();
$orderOutstanding = (float)$pdo->query("SELECT COALESCE(SUM(CASE WHEN COALESCE(settled, 0) = 0 THEN COALESCE(final_total, amount) - COALESCE((SELECT SUM(amount) FROM payments p WHERE p.order_id = orders.id), 0) ELSE 0 END), 0) FROM orders")->fetchColumn();
$totalOutstanding += $invoiceOutstanding + $orderOutstanding;
$counts['total_outstanding'] = $totalOutstanding;

$activity = [];
// Only include recent activity from the last 1 hour
$oneHour = "datetime('now', '-1 hour')";
foreach ($pdo->query("SELECT id, customer_name, COALESCE(final_total, total_amount) AS amount, created_at FROM invoices WHERE created_at >= $oneHour ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    // compute balance for the invoice
    $payStmt->execute([(int)$row['id']]);
    $paidInv = (float)$payStmt->fetchColumn();
    $balanceInv = max(0, (float)$row['amount'] - $paidInv);
    $detail = htmlspecialchars($row['customer_name']) . ' • TZS ' . number_format((float)$row['amount'], 0);
    if ($balanceInv > 0) {
        $detail .= ' • Balance TZS ' . number_format($balanceInv, 0);
    }
    $activity[] = [
        'id' => (int)$row['id'],
        'type' => 'Invoice',
        'title' => 'Invoice #' . htmlspecialchars($row['id']),
        'detail' => $detail,
        'balance' => $balanceInv,
        'time' => $row['created_at']
    ];
}
foreach ($pdo->query("SELECT id, customer, item, quantity, COALESCE(final_total, amount) AS amount, created_at FROM orders WHERE created_at >= $oneHour ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    // compute per-order profit where possible
    $orderProfit = 0;
    $pstmt = $pdo->prepare('SELECT buy_price, sell_price FROM products WHERE name = ? LIMIT 1');
    $pstmt->execute([$row['item']]);
    $pp = $pstmt->fetch(PDO::FETCH_ASSOC);
    if ($pp) $orderProfit = ((float)$pp['sell_price'] - (float)$pp['buy_price']) * (int)$row['quantity'];
    $paymentStmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM payments WHERE order_id = ?');
    $paymentStmt->execute([(int)$row['id']]);
    $paidOrder = (float)$paymentStmt->fetchColumn();
    $balanceOrder = max(0, (float)$row['amount'] - $paidOrder);
    $detail = htmlspecialchars($row['customer']) . ' • ' . htmlspecialchars($row['item']) . ' x' . (int)$row['quantity'] . ' • TZS ' . number_format((float)$row['amount'], 0);
    if ($balanceOrder > 0) {
        $detail .= ' • Balance TZS ' . number_format($balanceOrder, 0);
    }
    $activity[] = [
        'id' => (int)$row['id'],
        'type' => 'Order',
        'title' => 'Order #' . htmlspecialchars($row['id']),
        'detail' => $detail,
        'profit' => $orderProfit,
        'time' => $row['created_at']
    ];
}
foreach ($pdo->query("SELECT id, customer_name, amount, created_at FROM payments WHERE created_at >= $oneHour ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $activity[] = [
        'id' => (int)$row['id'],
        'type' => 'Payment',
        'title' => 'Payment #' . htmlspecialchars($row['id']),
        'detail' => htmlspecialchars($row['customer_name']) . ' • TZS ' . number_format((float)$row['amount'], 0),
        'time' => $row['created_at']
    ];
}

usort($activity, function ($a, $b) { return strcmp($b['time'], $a['time']); });
$activity = array_slice($activity, 0, 8);

header('Content-Type: application/json');
echo json_encode(['counts' => $counts, 'activity' => $activity]);
