<?php
require_once __DIR__ . '/session_bootstrap.php';
mitas_start_session();
require_once __DIR__ . '/auth.php';
$pdo = mitas_db();
mitas_ensure_user_schema($pdo);
mitas_require_permission($pdo, 'account.password.change');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$error = '';
$required = !empty($_SESSION['must_change_password']) || (($_GET['required'] ?? '') === '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!is_string($csrf) || !hash_equals($csrfToken, $csrf)) {
        http_response_code(400);
        $error = 'This form expired. Reload the page and try again.';
    } else {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        $stmt = $pdo->prepare('SELECT password_hash, session_version FROM users WHERE id = ? AND is_active = 1');
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $error = 'The current password is incorrect.';
        } elseif (!mitas_password_is_strong($newPassword)) {
            $error = 'Use at least 12 characters with uppercase, lowercase, a number, and a symbol.';
        } elseif (!hash_equals($newPassword, $confirmPassword)) {
            $error = 'The new passwords do not match.';
        } elseif (password_verify($newPassword, $user['password_hash'])) {
            $error = 'Choose a password different from your current one.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, session_version = session_version + 1, must_change_password = 0 WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int)$_SESSION['user_id']]);
            $_SESSION['user_session_version'] = (int)$user['session_version'] + 1;
            $_SESSION['must_change_password'] = 0;
            $_SESSION['flash'] = 'Password changed successfully.';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            session_regenerate_id(true);
            header('Location: index.php?password_changed=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Change password</title>
<link rel="stylesheet" href="styles.css">
</head>
<body class="guest-page">
<a class="skip-link" href="#main-content">Skip to content</a>
<main class="login-wrap" id="main-content">
<section class="panel login-panel role-form-panel">
<h1>Change password</h1>
<?php if ($required): ?><p role="status">Change your temporary password before continuing.</p><?php endif; ?>
<?php if ($error !== ''): ?><div class="flash" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="stacked-form">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<label for="currentPassword">Current password</label>
<input id="currentPassword" type="password" name="current_password" autocomplete="current-password" required>
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
<?php if (!$required): ?><p><a href="index.php">Back to dashboard</a></p><?php endif; ?>
<p id="passwordToolStatus" class="sr-only" aria-live="polite"></p>
</section>
</main>
<script src="password-tools.js" defer></script>
</body>
</html>
