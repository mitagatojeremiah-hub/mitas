<?php
function mitas_role_permissions(): array {
    return [
        'admin' => ['*'],
        'manager' => [
            'dashboard.view', 'inventory.view', 'inventory.manage', 'inventory.cost.view',
            'customers.view', 'customers.manage', 'suppliers.view', 'suppliers.manage',
            'purchases.view', 'purchases.manage', 'sales.view', 'sales.manage',
            'invoices.view', 'invoices.manage', 'payments.view', 'payments.manage',
            'adjustments.view', 'adjustments.manage', 'reports.view', 'reports.export',
            'layouts.manage', 'account.password.change'
        ],
        'staff' => [
            'dashboard.view', 'inventory.view', 'customers.view', 'customers.manage',
            'sales.view', 'sales.manage', 'invoices.view', 'invoices.manage',
            'payments.view', 'payments.manage', 'layouts.manage', 'account.password.change'
        ]
    ];
}

function mitas_ensure_user_schema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'admin',
        is_active INTEGER NOT NULL DEFAULT 1,
        email TEXT,
        email_verified_at TEXT,
        session_version INTEGER NOT NULL DEFAULT 0,
        must_change_password INTEGER NOT NULL DEFAULT 0
    )");
    $columns = array_column($pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    $additions = [
        'is_active' => 'INTEGER NOT NULL DEFAULT 1',
        'email' => 'TEXT',
        'email_verified_at' => 'TEXT',
        'session_version' => 'INTEGER NOT NULL DEFAULT 0',
        'must_change_password' => 'INTEGER NOT NULL DEFAULT 0'
    ];
    foreach ($additions as $column => $definition) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN {$column} {$definition}");
        }
    }
    mitas_ensure_email_token_schema($pdo);
}

function mitas_ensure_email_token_schema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS account_email_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        email TEXT NOT NULL,
        purpose TEXT NOT NULL,
        token_hash TEXT NOT NULL UNIQUE,
        expires_at TEXT NOT NULL,
        consumed_at TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
}

function mitas_password_is_strong(string $password): bool {
    return strlen($password) >= 12
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[^A-Za-z0-9]/', $password) === 1;
}

function mitas_issue_email_token(PDO $pdo, int $userId, string $email, string $purpose): string {
    if (!in_array($purpose, ['verify_email', 'password_reset'], true)) {
        throw new InvalidArgumentException('Unsupported email token purpose.');
    }
    mitas_ensure_email_token_schema($pdo);
    $expireSeconds = $purpose === 'verify_email' ? 86400 : 1800;
    $expireAt = date('Y-m-d H:i:s', time() + $expireSeconds);
    $invalidate = $pdo->prepare('UPDATE account_email_tokens SET consumed_at = CURRENT_TIMESTAMP WHERE user_id = ? AND purpose = ? AND consumed_at IS NULL');
    $invalidate->execute([$userId, $purpose]);
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('INSERT INTO account_email_tokens (user_id, email, purpose, token_hash, expires_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $email, $purpose, hash('sha256', $token), $expireAt]);
    return $token;
}

function mitas_find_email_token(PDO $pdo, string $token, string $purpose): ?array {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    mitas_ensure_email_token_schema($pdo);
    $stmt = $pdo->prepare('SELECT id, user_id, email, purpose FROM account_email_tokens WHERE token_hash = ? AND purpose = ? AND consumed_at IS NULL AND expires_at > ? LIMIT 1');
    $stmt->execute([hash('sha256', $token), $purpose, date('Y-m-d H:i:s')]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function mitas_consume_email_token(PDO $pdo, int $tokenId): bool {
    $stmt = $pdo->prepare('UPDATE account_email_tokens SET consumed_at = ? WHERE id = ? AND consumed_at IS NULL AND expires_at > ?');
    $now = date('Y-m-d H:i:s');
    $stmt->execute([$now, $tokenId, $now]);
    return $stmt->rowCount() === 1;
}

function mitas_send_account_email(string $to, string $token, string $purpose): bool {
    $apiKey = trim((string)getenv('RESEND_API_KEY'));
    $from = trim((string)getenv('RESEND_FROM_EMAIL'));
    $baseUrl = trim((string)getenv('APP_URL'));
    if ($baseUrl === '' && getenv('VERCEL_URL')) {
        $baseUrl = 'https://' . trim((string)getenv('VERCEL_URL'), '/');
    }
    if ($apiKey === '' || $from === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $parts = parse_url($baseUrl);
    if (!$parts || empty($parts['host']) || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['https', 'http'], true)) {
        return false;
    }
    if (strtolower((string)$parts['scheme']) !== 'https' && !in_array(strtolower((string)$parts['host']), ['localhost', '127.0.0.1'], true)) {
        return false;
    }

    $query = $purpose === 'verify_email' ? ['verify' => $token] : ['token' => $token];
    $url = rtrim($baseUrl, '/') . '/password_recovery.php?' . http_build_query($query);
    $isVerification = $purpose === 'verify_email';
    $subject = $isVerification ? 'Verify your Mitas email address' : 'Reset your Mitas password';
    $action = $isVerification ? 'Verify email address' : 'Reset password';
    $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $html = '<p>Use the secure link below to ' . strtolower($action) . ' for your Mitas account.</p>'
        . '<p><a href="' . $escapedUrl . '">' . htmlspecialchars($action, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a></p>'
        . '<p>This one-time link expires in ' . ($isVerification ? '24 hours' : '30 minutes') . '. If you did not request it, you can ignore this email.</p>';

    $payload = json_encode([
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $html
    ], JSON_UNESCAPED_SLASHES);
    if (!is_string($payload)) {
        return false;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer {$apiKey}\r\nContent-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    $response = @file_get_contents('https://api.resend.com/emails', false, $context);
    $status = 0;
    foreach (($http_response_header ?? []) as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $match)) {
            $status = (int)$match[1];
            break;
        }
    }
    return is_string($response) && $status >= 200 && $status < 300;
}

function mitas_current_user(PDO $pdo): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    mitas_ensure_user_schema($pdo);
    $stmt = $pdo->prepare('SELECT id, username, LOWER(TRIM(role)) AS role, is_active, session_version, must_change_password FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $sessionVersion = (int)($user['session_version'] ?? 0);

    if (
        !$user
        || (int)$user['is_active'] !== 1
        || !array_key_exists($user['role'], mitas_role_permissions())
        || !isset($_SESSION['user_session_version'])
        || (int)$_SESSION['user_session_version'] !== $sessionVersion
    ) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $_SESSION['user_session_version'], $_SESSION['must_change_password']);
        return null;
    }

    $_SESSION['user_role'] = $user['role'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['must_change_password'] = (int)$user['must_change_password'];
    return $user;
}

function mitas_can(string $permission): bool {
    $role = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
    $permissions = mitas_role_permissions()[$role] ?? [];
    return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
}

function mitas_require_permission(PDO $pdo, string $permission, bool $json = false): void {
    $user = mitas_current_user($pdo);
    if (!$user) {
        if ($json) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'not_authenticated']);
            exit;
        }
        header('Location: index.php');
        exit;
    }

    if (!empty($user['must_change_password']) && $permission !== 'account.password.change') {
        if ($json) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'password_change_required']);
        } else {
            header('Location: change_password.php?required=1');
        }
        exit;
    }

    if (!mitas_can($permission)) {
        http_response_code(403);
        if ($json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'forbidden']);
        } else {
            echo 'Forbidden';
        }
        exit;
    }
}
