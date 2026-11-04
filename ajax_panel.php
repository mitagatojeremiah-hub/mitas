<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
// Lightweight AJAX endpoint to return HTML fragments for panels
$panel = $_GET['panel'] ?? '';
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
require_once __DIR__ . '/auth.php';
$panelPermissions = [
    'inventory_list' => 'inventory.view',
    'customers' => 'customers.view',
    'suppliers' => 'suppliers.view',
    'purchases' => 'purchases.view',
    'payments' => 'payments.view',
    'adjustments' => 'adjustments.view',
    'invoices' => 'invoices.view',
    'recent_orders' => 'sales.view'
];
if (!isset($panelPermissions[$panel])) {
    http_response_code(404);
    exit('Panel not found');
}
mitas_require_permission($pdo, $panelPermissions[$panel], true);
$canViewCost = mitas_can('inventory.cost.view');

function esc($s){ return htmlspecialchars($s); }

$frag = '';
switch($panel){
    case 'inventory_list':
        $rows = $pdo->query('SELECT name, category, quantity, buy_price, sell_price, location FROM products ORDER BY name LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Name</th><th>Category</th><th>Qty</th>' . ($canViewCost ? '<th>Buy</th>' : '') . '<th>Sell</th><th>Location</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['name']).'</td><td>'.esc($r['category']).'</td><td>'.(int)$r['quantity'].'</td>' . ($canViewCost ? '<td>TZS '.number_format((float)$r['buy_price'],0).'</td>' : '') . '<td>TZS '.number_format((float)$r['sell_price'],0).'</td><td>'.esc($r['location']).'</td></tr>';
        }
        $frag .= '</tbody></table>';
        $frag .= '</div>';
        break;
    case 'customers':
        $rows = $pdo->query('SELECT name, phone, email, address FROM customers ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Address</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['name']).'</td><td>'.esc($r['phone']).'</td><td>'.esc($r['email']).'</td><td>'.esc($r['address']).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    case 'suppliers':
        $rows = $pdo->query('SELECT name, phone, email, address FROM suppliers ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Address</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['name']).'</td><td>'.esc($r['phone']).'</td><td>'.esc($r['email']).'</td><td>'.esc($r['address']).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    case 'purchases':
        $rows = $pdo->query('SELECT supplier_name, item, quantity, amount FROM purchases ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Supplier</th><th>Item</th><th>Qty</th><th>Amount</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['supplier_name']).'</td><td>'.esc($r['item']).'</td><td>'.(int)$r['quantity'].'</td><td>TZS '.number_format((float)$r['amount'],0).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    case 'payments':
        $rows = $pdo->query('SELECT invoice_id, customer_name, amount, method, created_at FROM payments ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['invoice_id']).'</td><td>'.esc($r['customer_name']).'</td><td>TZS '.number_format((float)$r['amount'],0).'</td><td>'.esc($r['method']).'</td><td>'.esc(substr($r['created_at'],0,10)).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    case 'adjustments':
        $rows = $pdo->query('SELECT product_id, quantity_change, reason, created_at FROM stock_adjustments ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Product ID</th><th>Change</th><th>Reason</th><th>Date</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.(int)$r['product_id'].'</td><td>'.(int)$r['quantity_change'].'</td><td>'.esc($r['reason']).'</td><td>'.esc(substr($r['created_at'],0,10)).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    case 'invoices':
        $rows = $pdo->query('SELECT invoice_number, customer_name, total_amount, status, created_at FROM invoices ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Invoice #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['invoice_number']).'</td><td>'.esc($r['customer_name']).'</td><td>TZS '.number_format((float)$r['total_amount'],0).'</td><td>'.esc($r['status']).'</td><td>'.esc(substr($r['created_at'],0,10)).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    case 'recent_orders':
        $rows = $pdo->query('SELECT customer, item, quantity, amount, created_at FROM orders ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $frag .= '<div class="panel-fragment">';
        $frag .= '<table><thead><tr><th>Customer</th><th>Item</th><th>Qty</th><th>Amount</th><th>Date</th></tr></thead><tbody>';
        foreach($rows as $r){
            $frag .= '<tr><td>'.esc($r['customer']).'</td><td>'.esc($r['item']).'</td><td>'.(int)$r['quantity'].'</td><td>TZS '.number_format((float)$r['amount'],0).'</td><td>'.esc(substr($r['created_at'],0,10)).'</td></tr>';
        }
        $frag .= '</tbody></table></div>';
        break;
    default:
        $frag = '<div class="panel-fragment">No panel or unsupported panel specified.</div>';
}

echo $frag;
