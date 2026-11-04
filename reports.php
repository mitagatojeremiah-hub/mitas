<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/translations.php';
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
mitas_require_permission($pdo, 'reports.view');

$defaultStart = date('Y-m-01', strtotime('first day of last month'));
$defaultEnd = date('Y-m-t', strtotime('last day of last month'));
$normalizeDate = static function ($value, string $fallback): string {
    if (!is_string($value)) {
        return $fallback;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
};
$start = $normalizeDate($_GET['start'] ?? null, $defaultStart);
$end = $normalizeDate($_GET['end'] ?? null, $defaultEnd);
$rangeSum = static function (string $sql) use ($pdo, $start, $end): float {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start, $end]);
    return (float)$stmt->fetchColumn();
};

$totalSales = $rangeSum('SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE date(created_at) BETWEEN ? AND ?');
$totalOrderSales = $rangeSum("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status = 'Sold' AND date(created_at) BETWEEN ? AND ?");
$totalPurchases = $rangeSum('SELECT COALESCE(SUM(amount),0) FROM purchases WHERE date(created_at) BETWEEN ? AND ?');
$totalPayments = $rangeSum('SELECT COALESCE(SUM(amount),0) FROM payments WHERE date(created_at) BETWEEN ? AND ?');

$stockValue = (float)$pdo->query('SELECT COALESCE(SUM(quantity * buy_price),0) FROM products')->fetchColumn();

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="<?= htmlspecialchars($language) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($labels['reports_title']) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: clamp(12px, 3vw, 24px); color: #102a43; }
    .card { background: #fff; padding: 16px; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,.06); margin-bottom: 12px; overflow-x: auto; }
    form { display: flex; flex-wrap: wrap; align-items: end; gap: 12px; }
    form label { display: grid; flex: 1 1 190px; gap: 6px; }
    input, button { min-height: 44px; max-width: 100%; font: inherit; }
    a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid #1f73d8; outline-offset: 3px; }
    .skip-link { position: absolute; z-index: 10; top: 8px; left: 8px; transform: translateY(-160%); padding: 10px 14px; border-radius: 8px; background: #fff; color: #102a43; box-shadow: 0 4px 18px rgba(15,23,42,.2); }
    .skip-link:focus { transform: translateY(0); }
    @media (max-width: 480px) {
      body { padding: 12px; }
      form label, form input, form button { width: 100%; }
      .card { padding: 12px; }
    }
  </style>
</head>
<body>
  <a class="skip-link" href="#main-content"><?= htmlspecialchars($labels['skip_to_content'] ?? 'Skip to content') ?></a>
  <main id="main-content">
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
  </main>
</body>
</html>
