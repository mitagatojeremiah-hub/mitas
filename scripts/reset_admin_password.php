<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/auth.php';

$username = trim((string)($argv[1] ?? ''));
$password = (string)getenv('MITAS_ADMIN_RECOVERY_PASSWORD');

if ($username === '' || strlen($username) > 64) {
    fwrite(STDERR, "Usage: set MITAS_ADMIN_RECOVERY_PASSWORD, then run php scripts/reset_admin_password.php <admin-username>\n");
    exit(2);
}
if (!mitas_password_is_strong($password)) {
    fwrite(STDERR, "MITAS_ADMIN_RECOVERY_PASSWORD must contain at least 12 characters with uppercase, lowercase, a number, and a symbol.\n");
    exit(2);
}

try {
    $pdo = mitas_db();
    mitas_ensure_user_schema($pdo);
    $stmt = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?, session_version = session_version + 1, must_change_password = 1
         WHERE username = ? AND LOWER(TRIM(role)) = 'admin' AND is_active = 1"
    );
    $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $username]);
    if ($stmt->rowCount() !== 1) {
        fwrite(STDERR, "No active Admin account matched that username; no password was changed.\n");
        exit(1);
    }
    fwrite(STDOUT, "Admin password reset. Sign in with the temporary value from MITAS_ADMIN_RECOVERY_PASSWORD and change it immediately.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Password reset failed. Check DATABASE_URL and database access; no credential values were printed.\n");
    exit(1);
} finally {
    unset($password);
}
