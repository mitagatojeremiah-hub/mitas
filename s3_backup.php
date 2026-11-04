<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
mitas_require_permission($pdo, 'backups.manage', true);
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid CSRF']);
    exit;
}

if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
    http_response_code(501);
    echo json_encode(['error' => 'SQLite file backup is not available for PostgreSQL. Use Supabase database backups.']);
    exit;
}
$dbFile = getenv('MITAS_SQLITE_PATH') ?: (__DIR__ . '/data/mitas_system.sqlite');
if (!is_file($dbFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'SQLite database file not found']);
    exit;
}

$bucket = getenv('S3_BUCKET');
$region = getenv('AWS_REGION') ?: 'us-east-1';
$keyPrefix = getenv('S3_PREFIX') ?: 'backups/';

if (!$bucket) {
    http_response_code(500);
    echo json_encode(['error' => 'S3_BUCKET is not configured']);
    exit;
}

$timestamp = date('Ymd-His');
$key = rtrim($keyPrefix, '/') . "/mitas_system_{$timestamp}.sqlite";

$s3 = new S3Client([
    'version' => 'latest',
    'region' => $region,
]);

try {
    $result = $s3->putObject([
        'Bucket' => $bucket,
        'Key' => $key,
        'SourceFile' => $dbFile,
        'ServerSideEncryption' => 'AES256',
    ]);
    echo json_encode(['success' => true, 'location' => $result['ObjectURL']]);
} catch (S3Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'S3 upload failed', 'message' => $e->getMessage()]);
}
