<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$dbFile = __DIR__ . '/data/mitas_system.sqlite';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

session_start();

if (empty($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
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
