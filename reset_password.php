<?php require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = '';
$owner = null; /* |-------------------------------------------------------------------------- | Validate reset token |-------------------------------------------------------------------------- */
if ($token !== '') {
    $token_hash = hash('sha256', $token);
    $stmt = get_db()->prepare('SELECT id, username FROM store_owner WHERE reset_token_hash = ? AND reset_token_expires_at IS NOT NULL AND reset_token_expires_at > NOW() LIMIT 1');
    $stmt->execute([$token_hash]);
    $owner = $stmt->fetch();
}
if (!$owner) {
    $error = 'This password reset link is invalid or has expired.';
} /* |-------------------------------------------------------------------------- | Process new password |-------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $owner) {
    csrf_check();
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($new_password === '') {
        $error = 'Please enter a new password.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Your new password must be at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'The passwords do not match.';
    } else { /* * Hash the new password securely. */
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT); /* * Update the password and immediately invalidate * the reset token so it cannot be reused. */
        $update = get_db()->prepare('UPDATE store_owner SET password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?');
        $update->execute([$password_hash, $owner['id']]);
        $success = 'Your password has been reset successfully.';
    }
}
$pageTitle = 'Reset Password';
require __DIR__ . '/includes/header.php'; ?>
<div class="login-wrap"> <img src="assets/images/omar-logo.webp" alt="<?= e(STORE_NAME) ?>" class="login-logo">
    <?php if ($error): ?>
        <div class="alert alert-error"> <?= e($error) ?> </div> <?php if (!$owner): ?>
            <div class="card">
                <p> Please request a new password reset link. </p> <a href="forgot_password.php"
                    class="btn btn-primary btn-block"> Request New Reset Link </a> <a href="login.php" class="forgot-password">
                    Back to Login </a>
            </div> <?php endif; ?> <?php elseif ($success): ?>
        <div class="alert alert-success"> <?= e($success) ?> </div>
        <div class="card">
            <p> You can now log in using your new password. </p> <a href="login.php" class="btn btn-primary btn-block"> Go
                to Login </a>
        </div> <?php else: ?>
        <form method="post" class="card">
            <h2>Reset Password</h2>
            <p> Create a new password for your account. </p> <input type="hidden" name="csrf_token"
                value="<?= e(csrf_token()) ?>"> <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="field"> <label for="new_password"> New Password </label>
                <div class="password-wrapper"> <input type="password" id="new_password" name="new_password" minlength="8"
                        autocomplete="new-password" required> <button type="button" class="password-toggle"
                        onclick="togglePassword('new_password', this)" aria-label="Show password"> 👁 </button> </div>
            </div>
            <div class="field"> <label for="confirm_password"> Confirm New Password </label>
                <div class="password-wrapper"> <input type="password" id="confirm_password" name="confirm_password"
                        minlength="8" autocomplete="new-password" required> <button type="button" class="password-toggle"
                        onclick="togglePassword('confirm_password', this)" aria-label="Show password"> 👁 </button> </div>
            </div> <button type="submit" class="btn btn-primary btn-block"> Reset Password </button> <a href="login.php"
                class="forgot-password"> Back to Login </a>
        </form> <?php endif; ?>
</div>
<script> function togglePassword(inputId, button) { const password = document.getElementById(inputId); if (password.type === 'password') { password.type = 'text'; button.textContent = '🙈'; button.setAttribute('aria-label', 'Hide password'); } else { password.type = 'password'; button.textContent = '👁'; button.setAttribute('aria-label', 'Show password'); } } </script>
<?php require __DIR__ . '/includes/footer.php'; ?>