<?php
declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';

$pdo = mitas_db();
mitas_ensure_user_schema($pdo);

header('Cache-Control: no-store, max-age=0');
header('Pragma: no-cache');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = (string)$_SESSION['csrf_token'];
$notice = '';
$error = '';
$mode = 'request';
$token = '';
$tokenRecord = null;

if (isset($_GET['verify']) && is_string($_GET['verify'])) {
    $mode = 'verify';
    $token = $_GET['verify'];
    $tokenRecord = mitas_find_email_token($pdo, $token, 'verify_email');
} elseif (isset($_GET['token']) && is_string($_GET['token'])) {
    $mode = 'reset';
    $token = $_GET['token'];
    $tokenRecord = mitas_find_email_token($pdo, $token, 'password_reset');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedCsrf = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedCsrf) || !hash_equals($csrfToken, $submittedCsrf)) {
        http_response_code(400);
        $error = 'This form expired. Reload the page and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'request_reset') {
            $username = trim((string)($_POST['username'] ?? ''));
            if ($username !== '' && strlen($username) <= 64) {
                $stmt = $pdo->prepare('SELECT id, email FROM users WHERE username = ? AND is_active = 1 AND email IS NOT NULL AND email_verified_at IS NOT NULL LIMIT 1');
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    $since = date('Y-m-d H:i:s', time() - 3600);
                    $limitStmt = $pdo->prepare("SELECT COUNT(*) FROM account_email_tokens WHERE user_id = ? AND purpose = 'password_reset' AND created_at > ?");
                    $limitStmt->execute([(int)$user['id'], $since]);
                    if ((int)$limitStmt->fetchColumn() < 3) {
                        $resetToken = mitas_issue_email_token($pdo, (int)$user['id'], (string)$user['email'], 'password_reset');
                        mitas_send_account_email((string)$user['email'], $resetToken, 'password_reset');
                    }
                }
            }
            $_SESSION['recovery_notice'] = 'If that username has an active account with a verified email, password reset instructions will be sent.';
            header('Location: password_recovery.php?requested=1');
            exit;
        }

        if ($action === 'confirm_email' || $action === 'reset_password') {
            $token = is_string($_POST['token'] ?? null) ? (string)$_POST['token'] : '';
            $purpose = $action === 'confirm_email' ? 'verify_email' : 'password_reset';
            $tokenRecord = mitas_find_email_token($pdo, $token, $purpose);
            $mode = $purpose === 'verify_email' ? 'verify' : 'reset';

            if (!$tokenRecord) {
                $error = 'This link is invalid, expired, or already used. Request a new link and try again.';
            } elseif ($action === 'confirm_email') {
                try {
                    $pdo->beginTransaction();
                    $claimed = mitas_consume_email_token($pdo, (int)$tokenRecord['id']);
                    $stmt = $pdo->prepare('UPDATE users SET email_verified_at = ? WHERE id = ? AND email = ? AND is_active = 1');
                    $stmt->execute([date('Y-m-d H:i:s'), (int)$tokenRecord['user_id'], (string)$tokenRecord['email']]);
                    if (!$claimed || $stmt->rowCount() !== 1) {
                        $pdo->rollBack();
                        $tokenRecord = null;
                        $error = 'This link is invalid, expired, or already used. Request a new link and try again.';
                    } else {
                        $pdo->commit();
                        $_SESSION['recovery_notice'] = 'Email verified. You can now request a password reset link.';
                        header('Location: password_recovery.php?verified=1');
                        exit;
                    }
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $e;
                }
            } else {
                $newPassword = (string)($_POST['new_password'] ?? '');
                $confirmPassword = (string)($_POST['confirm_password'] ?? '');
                if (!mitas_password_is_strong($newPassword)) {
                    $error = 'Use at least 12 characters with uppercase, lowercase, a number, and a symbol.';
                } elseif (!hash_equals($newPassword, $confirmPassword)) {
                    $error = 'The new passwords do not match.';
                } else {
                    try {
                        $pdo->beginTransaction();
                        $claimed = mitas_consume_email_token($pdo, (int)$tokenRecord['id']);
                        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, session_version = session_version + 1, must_change_password = 0 WHERE id = ? AND email = ? AND email_verified_at IS NOT NULL AND is_active = 1');
                        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int)$tokenRecord['user_id'], (string)$tokenRecord['email']]);
                        if (!$claimed || $stmt->rowCount() !== 1) {
                            $pdo->rollBack();
                            $tokenRecord = null;
                            $error = 'This link is invalid, expired, or already used. Request a new link and try again.';
                        } else {
                            $pdo->commit();
                            $_SESSION['recovery_notice'] = 'Password reset successfully. Sign in with your new password.';
                            header('Location: password_recovery.php?reset=1');
                            exit;
                        }
                    } catch (Throwable $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        throw $e;
                    }
                }
            }
        }
    }
}

if (isset($_SESSION['recovery_notice'])) {
    $notice = (string)$_SESSION['recovery_notice'];
    unset($_SESSION['recovery_notice']);
}
if (isset($_GET['requested'])) {
    $notice = 'If that username has an active account with a verified email, password reset instructions will be sent.';
} elseif (isset($_GET['verified'])) {
    $notice = 'Email verified. You can now request a password reset link.';
} elseif (isset($_GET['reset'])) {
    $notice = 'Password reset successfully. Sign in with your new password.';
}

$tokenIsUsable = false;
if ($tokenRecord && $mode === 'verify') {
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE id = ? AND email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([(int)$tokenRecord['user_id'], (string)$tokenRecord['email']]);
    $tokenIsUsable = (bool)$stmt->fetchColumn();
} elseif ($tokenRecord && $mode === 'reset') {
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE id = ? AND email = ? AND email_verified_at IS NOT NULL AND is_active = 1 LIMIT 1');
    $stmt->execute([(int)$tokenRecord['user_id'], (string)$tokenRecord['email']]);
    $tokenIsUsable = (bool)$stmt->fetchColumn();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Account recovery</title>
<link rel="stylesheet" href="styles.css">
</head>
<body class="guest-page">
<a class="skip-link" href="#main-content">Skip to content</a>
<main class="login-wrap" id="main-content">
<section class="panel login-panel role-form-panel">
<h1>Account recovery</h1>
<p>Use your username to request a secure link sent to the verified email address on your account.</p>
<?php if ($notice !== ''): ?><div class="flash" role="status" aria-live="polite"><?= htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="flash" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div><?php endif; ?>

<?php if ($mode === 'verify' && $tokenIsUsable): ?>
    <h2>Verify your email</h2>
    <p>Confirm that you control the email address associated with this account.</p>
    <form method="post" class="stacked-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="action" value="confirm_email">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <button type="submit">Verify email address</button>
    </form>
<?php elseif ($mode === 'reset' && $tokenIsUsable): ?>
    <h2>Choose a new password</h2>
    <form method="post" class="stacked-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <label for="newPassword">New password</label>
        <input id="newPassword" type="password" name="new_password" autocomplete="new-password" minlength="12" required>
        <div class="password-tools">
            <button type="button" data-generate-password="newPassword">Generate strong password</button>
            <button type="button" data-toggle-password="newPassword" aria-pressed="false">Show</button>
        </div>
        <label for="confirmPassword">Confirm new password</label>
        <input id="confirmPassword" type="password" name="confirm_password" autocomplete="new-password" minlength="12" required>
        <button type="submit">Save new password</button>
    </form>
    <p id="passwordToolStatus" class="sr-only" aria-live="polite"></p>
<?php elseif ($mode === 'verify' || $mode === 'reset'): ?>
    <p role="status">This link is invalid, expired, or already used. Request a new link and try again.</p>
<?php else: ?>
    <form method="post" class="stacked-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="action" value="request_reset">
        <label for="username">Username</label>
        <input id="username" type="text" name="username" autocomplete="username" maxlength="64" required>
        <button type="submit">Send reset link</button>
    </form>
<?php endif; ?>

<p><a href="index.php">Back to sign in</a></p>
</section>
</main>
<script src="password-tools.js" defer></script>
</body>
</html>
