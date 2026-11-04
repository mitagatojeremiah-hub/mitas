<?php
session_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/translations.php';
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if (PHP_SAPI !== 'cli') {
    mitas_require_permission($pdo, 'invoices.view');
}

$invoiceId = (int)($_GET['invoice'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM invoices WHERE id = ?');
$stmt->execute([$invoiceId]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invoice) {
    exit('Invoice not found.');
}
// fetch invoice items if any
$itemStmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
$itemStmt->execute([$invoiceId]);
$invoiceItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

$companyProfile = $pdo->query('SELECT * FROM company_profile ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
if (!$companyProfile) {
    $pdo->exec("INSERT INTO company_profile (company_name, tagline, address, pobox, phone, email, website, logo_path) VALUES ('Mitas Business Solutions','Reliable business and inventory services','Dar es Salaam, Tanzania','P.O. Box 12345','+255 712 345 678','info@mitas.co.tz','www.mitas.co.tz','logo.svg')");
    $companyProfile = $pdo->query('SELECT * FROM company_profile ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
}
$companyName = $companyProfile['company_name'] ?? 'Mitas Business Solutions';
$companyTagline = $companyProfile['tagline'] ?? '';
$companyAddress = $companyProfile['address'] ?? '';
$companyPob = $companyProfile['pobox'] ?? '';
$companyPhone = $companyProfile['phone'] ?? '';
$companyEmail = $companyProfile['email'] ?? '';
$companyWebsite = $companyProfile['website'] ?? '';
$logoPath = $companyProfile['logo_path'] ?? 'logo.svg';
$logoFile = __DIR__ . '/' . ltrim($logoPath, '/');
$logoHtml = file_exists($logoFile) ? '<img src="' . htmlspecialchars($logoPath) . '" alt="Company logo" style="height:70px; width:auto;">' : '<div style="width:70px;height:70px;border-radius:50%;background:#1f73d8;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:bold;">MB</div>';
$paymentStmt = $pdo->prepare('SELECT * FROM payments WHERE invoice_id = ? ORDER BY created_at ASC');
$paymentStmt->execute([$invoiceId]);
$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);
$paidTotal = 0;
foreach ($payments as $payment) {
    $paidTotal += (float)$payment['final_amount'];
}
$invoiceTotal = (float)$invoice['final_total'] > 0 ? (float)$invoice['final_total'] : (float)$invoice['total_amount'];
$balanceDue = max(0, $invoiceTotal - $paidTotal);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($labels['invoice']) ?> <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; color: #102a43; background: #fff; }
        .box { border: 1px solid #cbd5e1; padding: 24px; border-radius: 10px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 2px solid #1f73d8; padding-bottom: 16px; margin-bottom: 18px; }
        .company { display: flex; gap: 12px; align-items: center; }
        .meta { text-align: right; }
        .meta p, .details p { margin: 4px 0; }
        .details { display: grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .badge { display: inline-block; background: #e8f1ff; color: #1f73d8; padding: 4px 8px; border-radius: 999px; font-weight: 600; font-size: 0.9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 10px; text-align: left; }
        th { background: #f8fafc; }
        .total { text-align: right; font-weight: 700; margin-top: 12px; }
        .box { max-width: 1100px; margin: 0 auto; overflow-x: auto; }
        @media (max-width: 640px) {
            body { padding: 10px; }
            .box { padding: 12px; }
            .header { flex-direction: column; gap: 12px; }
            .company { flex-wrap: wrap; }
            .meta { text-align: left; }
            .details { grid-template-columns: minmax(0, 1fr); }
            table { min-width: 600px; }
            th, td { padding: 8px; }
        }
        @media print {
            body { padding: 0; }
            .box { max-width: none; border: 0; }
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="header">
            <div class="company">
                <?= $logoHtml ?>
                <div>
                    <h2><?= htmlspecialchars($companyName) ?></h2>
                    <p><?= htmlspecialchars($companyTagline) ?></p>
                    <p><?= htmlspecialchars($companyAddress) ?> • <?= htmlspecialchars($companyPob) ?></p>
                    <p><?= htmlspecialchars($companyPhone) ?> • <?= htmlspecialchars($companyEmail) ?> • <?= htmlspecialchars($companyWebsite) ?></p>
                </div>
            </div>
            <div class="meta">
                <p><span class="badge"><?= htmlspecialchars($labels['invoice']) ?></span></p>
                <p><strong><?= htmlspecialchars($labels['invoice_no']) ?>:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
                <p><strong><?= htmlspecialchars($labels['date']) ?>:</strong> <?= htmlspecialchars(substr($invoice['created_at'], 0, 10)) ?></p>
                <p><strong><?= htmlspecialchars($labels['status']) ?>:</strong> <?= htmlspecialchars($invoice['status']) ?></p>
            </div>
        </div>
        <div class="details">
            <div>
                <p><strong><?= htmlspecialchars($labels['customer']) ?>:</strong> <?= htmlspecialchars($invoice['customer_name']) ?></p>
                <p><strong><?= htmlspecialchars($labels['document_type']) ?>:</strong> <?= htmlspecialchars($labels['invoice']) ?></p>
            </div>
            <div>
                <p><strong><?= htmlspecialchars($labels['reference']) ?>:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
                <p><strong><?= htmlspecialchars($labels['prepared']) ?>:</strong> <?= htmlspecialchars(substr($invoice['created_at'], 0, 10)) ?></p>
            </div>
        </div>
        <p><strong><?= htmlspecialchars($labels['notes']) ?>:</strong> <?= htmlspecialchars($invoice['notes'] ?? '') ?></p>
        <table>
            <tr>
                <th><?= htmlspecialchars($labels['description']) ?></th>
                <th><?= htmlspecialchars($labels['category']) ?></th>
                <th><?= htmlspecialchars($labels['qty']) ?></th>
                <th><?= htmlspecialchars($labels['unit_price']) ?></th>
                <th><?= htmlspecialchars($labels['discount']) ?></th>
                <th><?= htmlspecialchars($labels['amount']) ?></th>
                <th><?= htmlspecialchars($labels['manufacturer']) ?></th>
                <th><?= htmlspecialchars($labels['grade']) ?></th>
                <th><?= htmlspecialchars($labels['condition']) ?></th>
            </tr>
            <?php if (!empty($invoiceItems)): ?>
                <?php foreach ($invoiceItems as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['item']) ?></td>
                        <td><?= htmlspecialchars($it['category']) ?></td>
                        <td><?= (int)$it['quantity'] ?></td>
                        <td>TZS <?= number_format((float)$it['unit_price'], 0) ?></td>
                        <td><?= number_format((float)$it['discount_percent'], 2) ?>%</td>
                        <td>TZS <?= number_format((float)$it['amount'], 0) ?></td>
                        <td><?= htmlspecialchars($it['manufacturer'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($it['grade'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($it['condition'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td><?= htmlspecialchars($invoice['item']) ?></td>
                    <td></td>
                    <td>1</td>
                    <td>TZS <?= number_format((float)$invoice['total_amount'], 0) ?></td>
                    <td>0%</td>
                    <td>TZS <?= number_format((float)$invoice['total_amount'], 0) ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            <?php endif; ?>
        </table>
        <div class="total"><?= htmlspecialchars($labels['total_amount']) ?>: TZS <?= number_format($invoiceTotal, 0) ?></div>
        <?php if ($paidTotal > 0): ?>
            <div class="total"><?= htmlspecialchars($labels['paid_amount']) ?>: TZS <?= number_format($paidTotal, 0) ?></div>
            <div class="total"><?= htmlspecialchars($labels['balance_due']) ?>: TZS <?= number_format($balanceDue, 0) ?></div>
        <?php endif; ?>
        <?php if (!empty($payments)): ?>
            <h3 style="margin-top:24px;"><?= htmlspecialchars($labels['payment_history']) ?></h3>
            <table>
                <tr>
                    <th><?= htmlspecialchars($labels['date']) ?></th>
                    <th><?= htmlspecialchars($labels['amount']) ?></th>
                    <th>VAT</th>
                    <th><?= htmlspecialchars($labels['total_amount']) ?></th>
                    <th><?= htmlspecialchars($labels['method']) ?></th>
                    <th><?= htmlspecialchars($labels['reference_number']) ?></th>
                    <th><?= htmlspecialchars($labels['sender']) ?></th>
                </tr>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($payment['created_at'], 0, 10)) ?></td>
                        <td>TZS <?= number_format((float)$payment['amount'], 0) ?></td>
                        <td>TZS <?= number_format((float)$payment['vat_amount'], 0) ?></td>
                        <td>TZS <?= number_format((float)$payment['final_amount'], 0) ?></td>
                        <td><?= htmlspecialchars($payment['method']) ?></td>
                        <td><?= htmlspecialchars($payment['reference_number']) ?></td>
                        <td><?= htmlspecialchars($payment['sender_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
