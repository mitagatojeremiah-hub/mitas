<?php
session_start();
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Use POST']);
    exit;
}
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
    exit;
}
$backupDir = __DIR__ . '/data/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
$time = date('Ymd_His');
$dest = $backupDir . '/mitas_system_' . $time . '.sqlite';
if (!file_exists($dbFile)) {
    echo json_encode(['ok' => false, 'error' => 'Database not found']);
    exit;
}
if (!copy($dbFile, $dest)) {
    echo json_encode(['ok' => false, 'error' => 'Copy failed']);
    exit;
}
// keep last 30 backups
$files = glob($backupDir . '/mitas_system_*.sqlite');
usort($files, function($a,$b){ return filemtime($b) - filemtime($a); });
$keep = 30;
foreach (array_slice($files, $keep) as $old) { @unlink($old); }
echo json_encode(['ok' => true, 'backup' => basename($dest)]);
