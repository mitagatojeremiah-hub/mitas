<?php
session_start();
require_once __DIR__ . '/translations.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

$dbFile = __DIR__ . '/data/mitas_system.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$defaultStart = date('Y-m-01', strtotime('first day of last month'));
$defaultEnd = date('Y-m-t', strtotime('last day of last month'));
$start = $_GET['start'] ?? $defaultStart;
$end = $_GET['end'] ?? $defaultEnd;

$totalSales = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE date(created_at) BETWEEN '". $start ."' AND '". $end ."'")->fetchColumn();
$totalOrderSales = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status = 'Sold' AND date(created_at) BETWEEN '". $start ."' AND '". $end ."'")->fetchColumn();
$totalPurchases = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM purchases WHERE date(created_at) BETWEEN '". $start ."' AND '". $end ."'")->fetchColumn();
$totalPayments = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE date(created_at) BETWEEN '". $start ."' AND '". $end ."'")->fetchColumn();

$stockValue = (float)$pdo->query('SELECT COALESCE(SUM(quantity * buy_price),0) FROM products')->fetchColumn();

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($labels['reports_title']) ?></title>
  <style>
    body{font-family: Arial, sans-serif;padding:20px;color:#102a43}
    .card{background:#fff;padding:16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.06);margin-bottom:12px}
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($labels['reports_title']) ?></h1>
  <form method="get">
    <label><?= htmlspecialchars($labels['start_date']) ?> <input type="date" name="start" value="<?= htmlspecialchars($start) ?>"></label>
    <label><?= htmlspecialchars($labels['end_date']) ?> <input type="date" name="end" value="<?= htmlspecialchars($end) ?>"></label>
    <button type="submit"><?= htmlspecialchars($labels['apply'] ?? 'Apply') ?></button>
  </form>

  <div class="card">
    <h3><?= htmlspecialchars($labels['summary']) ?> (<?= htmlspecialchars($start) ?> to <?= htmlspecialchars($end) ?>)</h3>
    <ul>
      <li><?= htmlspecialchars($labels['total_sales']) ?>: TZS <?= number_format($totalSales,0) ?></li>
      <li><?= htmlspecialchars($labels['total_purchases']) ?>: TZS <?= number_format($totalPurchases,0) ?></li>
      <li><?= htmlspecialchars($labels['total_payments']) ?>: TZS <?= number_format($totalPayments,0) ?></li>
      <li><?= htmlspecialchars($labels['inventory_value']) ?>: TZS <?= number_format($stockValue,0) ?></li>
    </ul>
  </div>

  <div class="card">
    <h3><?= htmlspecialchars($labels['export']) ?></h3>
    <p><a href="reports_export.php?start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>"><?= htmlspecialchars($labels['download_csv']) ?></a></p>
    <p><button id="downloadPdf" type="button"><?= htmlspecialchars($labels['download_pdf']) ?></button></p>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    const reportData = {
      start: <?= json_encode($start) ?>,
      end: <?= json_encode($end) ?>,
      totalSales: <?= json_encode(number_format($totalSales, 0)) ?>,
      totalOrderSales: <?= json_encode(number_format($totalOrderSales, 0)) ?>,
      totalPurchases: <?= json_encode(number_format($totalPurchases, 0)) ?>,
      totalPayments: <?= json_encode(number_format($totalPayments, 0)) ?>,
      stockValue: <?= json_encode(number_format($stockValue, 0)) ?>
    };

    document.getElementById('downloadPdf').addEventListener('click', async () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      const title = `Mitas Summary ${reportData.start} to ${reportData.end}`;
      const fileName = `mitas_report_${reportData.start}_${reportData.end}.pdf`;
      doc.setFontSize(18);
      doc.text('Mitas Monthly System Summary', 20, 20);
      doc.setFontSize(12);
      doc.text(`Period: ${reportData.start} to ${reportData.end}`, 20, 32);
      doc.text(`Generated: ${new Date().toLocaleDateString()}`, 20, 40);
      doc.text(' ', 20, 48);
      doc.text(`Total Invoice Sales: TZS ${reportData.totalSales}`, 20, 56);
      doc.text(`Total Order Sales: TZS ${reportData.totalOrderSales}`, 20, 64);
      doc.text(`Total Purchases: TZS ${reportData.totalPurchases}`, 20, 72);
      doc.text(`Total Payments Received: TZS ${reportData.totalPayments}`, 20, 80);
      doc.text(`Inventory Value: TZS ${reportData.stockValue}`, 20, 88);
      doc.text(' ', 20, 96);
      doc.setFontSize(10);
      doc.text('This monthly summary report is generated from the system data and can be saved as a PDF.', 20, 104);
      doc.save(fileName);
    });
  </script>
</body>
</html>
