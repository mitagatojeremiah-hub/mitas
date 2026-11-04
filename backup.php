<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';

$pdo = mitas_db();
mitas_require_permission($pdo, 'backups.manage', true);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use POST']);
    exit;
}
$csrf = $_POST['csrf_token'] ?? '';
if (!is_string($csrf) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
    exit;
}
if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
    http_response_code(501);
    echo json_encode(['ok' => false, 'error' => 'File backup is only available for local SQLite. Use Supabase database backups for PostgreSQL.']);
    exit;
}

$dbFile = getenv('MITAS_SQLITE_PATH') ?: (__DIR__ . '/data/mitas_system.sqlite');
if (!is_file($dbFile)) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Database not found']);
    exit;
}
$backupDir = __DIR__ . '/data/backups';
if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to create backup directory']);
    exit;
}

$time = date('Ymd_His');
$dest = $backupDir . '/mitas_system_' . $time . '.sqlite';
if (!copy($dbFile, $dest)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Copy failed']);
    exit;
}
$files = glob($backupDir . '/mitas_system_*.sqlite') ?: [];
usort($files, static fn($a, $b) => filemtime($b) <=> filemtime($a));
foreach (array_slice($files, 30) as $old) {
    @unlink($old);
}
echo json_encode(['ok' => true, 'backup' => basename($dest)]);
