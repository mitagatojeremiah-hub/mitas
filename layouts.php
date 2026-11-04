<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
header('Content-Type: application/json; charset=utf-8');
$dbFile = __DIR__ . '/data/mitas_system.sqlite';
require_once __DIR__ . '/db.php';
$pdo = mitas_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . '/auth.php';
mitas_require_permission($pdo, 'layouts.manage', true);

// read JSON body if provided
$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: $_POST;
$action = $_GET['action'] ?? ($body['action'] ?? 'list');
$userId = (int)$_SESSION['user_id'];

// CSRF: expect csrf in body or post
$csrf = $body['csrf'] ?? $_POST['csrf_token'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf_token']) || !$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        http_response_code(400);
        echo json_encode(['error'=>'bad_csrf']);
        exit;
    }
}

if ($action === 'save') {
    $name = trim($body['name'] ?? '');
    $json = $body['json'] ?? '';
    if ($name === '' || $json === '') { http_response_code(400); echo json_encode(['error'=>'missing']); exit; }
    $stmt = $pdo->prepare('INSERT INTO layouts (user_id, name, json) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $name, $json]);
    echo json_encode(['ok'=>1,'id'=>$pdo->lastInsertId()]);
    exit;
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? $body['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'invalid_id']); exit; }
    $stmt = $pdo->prepare('DELETE FROM layouts WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    echo json_encode(['ok'=>1]);
    exit;
}

if (($action === 'get' || $action === 'load') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT id, name, json, created_at FROM layouts WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['error'=>'not_found']); exit; }
    echo json_encode(['ok'=>1,'layout'=>$row]);
    exit;
}

// default: list
$stmt = $pdo->prepare('SELECT id, name, created_at FROM layouts WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['ok'=>1,'layouts'=>$rows]);
