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
            'layouts.manage'
        ],
        'staff' => [
            'dashboard.view', 'inventory.view', 'customers.view', 'customers.manage',
            'sales.view', 'sales.manage', 'invoices.view', 'invoices.manage',
            'payments.view', 'payments.manage', 'layouts.manage'
        ]
    ];
}

function mitas_ensure_user_schema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'admin',
        is_active INTEGER NOT NULL DEFAULT 1
    )");
    $columns = array_column($pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('is_active', $columns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active INTEGER NOT NULL DEFAULT 1");
    }
}

function mitas_current_user(PDO $pdo): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    mitas_ensure_user_schema($pdo);
    $stmt = $pdo->prepare('SELECT id, username, LOWER(TRIM(role)) AS role, is_active FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || (int)$user['is_active'] !== 1 || !array_key_exists($user['role'], mitas_role_permissions())) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role']);
        return null;
    }

    $_SESSION['user_role'] = $user['role'];
    $_SESSION['username'] = $user['username'];
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
