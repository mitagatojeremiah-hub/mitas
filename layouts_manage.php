<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/translations.php';
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
mitas_require_permission($pdo, 'layouts.manage');
$csrf = $_SESSION['csrf_token'] ?? '';
$rows = $pdo->prepare('SELECT id, name, created_at FROM layouts WHERE user_id = ? ORDER BY created_at DESC');
$rows->execute([$_SESSION['user_id']]);
$list = $rows->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="<?= htmlspecialchars($language) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($labels['manage_layouts'] ?? 'Manage Layouts') ?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <main id="main-content">
    <h2><?= htmlspecialchars($labels['saved_layouts'] ?? 'Saved Layouts') ?></h2>
    <p><a href="index.php"><?= htmlspecialchars($labels['back_to_dashboard'] ?? 'Back to dashboard') ?></a></p>
    <div>
        <?php if (count($list) === 0): ?>
            <p><?= htmlspecialchars($labels['no_layouts_saved'] ?? 'No layouts saved yet.') ?></p>
        <?php else: ?>
            <ul>
                <?php foreach ($list as $l): ?>
                    <li>
                        <strong><?= htmlspecialchars($l['name']) ?></strong> (<?= htmlspecialchars($l['created_at']) ?>)
                        <button onclick="loadLayout(<?= (int)$l['id'] ?>)"><?= htmlspecialchars($labels['load'] ?? 'Load') ?></button>
                        <button onclick="exportLayout(<?= (int)$l['id'] ?>)"><?= htmlspecialchars($labels['export'] ?? 'Export') ?></button>
                        <button onclick="deleteLayout(<?= (int)$l['id'] ?>)"><?= htmlspecialchars($labels['delete'] ?? 'Delete') ?></button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <script>
    const CSRF = <?= json_encode($csrf) ?>;
    function loadLayout(id){ fetch('layouts.php?id='+encodeURIComponent(id)).then(r=>r.json()).then(j=>{ if (!j.ok) { alert('Load failed'); return; } const obj = JSON.parse(j.layout.json); if (obj.hidden) localStorage.setItem('mitas_hidden_panels_v1', JSON.stringify(obj.hidden)); if (obj.pinned) localStorage.setItem('mitas_pinned_panels_v1', JSON.stringify(obj.pinned)); if (obj.intervals) localStorage.setItem('mitas_panel_intervals_v1', JSON.stringify(obj.intervals)); if (typeof obj.edge_open !== 'undefined') localStorage.setItem('mitas_edge_open', obj.edge_open); alert('Layout applied. Reloading.'); location.href='index.php'; }); }
    function exportLayout(id){ fetch('layouts.php?id='+encodeURIComponent(id)).then(r=>r.json()).then(j=>{ if (!j.ok) { alert('Failed'); return; } const blob = new Blob([j.layout.json], {type:'application/json'}); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'layout_'+id+'.json'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); }); }
    function deleteLayout(id){ if (!confirm('Delete layout?')) return; fetch('layouts.php?action=delete', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id, csrf: CSRF }) }).then(r=>r.json()).then(j=>{ if (j.ok) location.reload(); else alert('Delete failed'); }); }
    </script>
    </main>
</body>
</html>
