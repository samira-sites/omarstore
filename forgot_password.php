<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/send_reset_email.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    $identifier = trim($_POST['identifier'] ?? '');

    if ($identifier === '') {
        $error = 'Please enter your username or email address.';
    } else {

        $stmt = get_db()->prepare(
            'SELECT id, username, email
             FROM store_owner
             WHERE username = ? OR email = ?
             LIMIT 1'
        );

        $stmt->execute([$identifier, $identifier]);
        $owner = $stmt->fetch();

        /*
         * Always show the same message whether the account
         * exists or not. This helps prevent account enumeration.
         */
        if ($owner && !empty($owner['email'])) {

            // Generate a secure random reset token.
            $reset_token = bin2hex(random_bytes(32));

            // Store only the hash of the token in the database.
            $reset_token_hash = hash('sha256', $reset_token);

            // Token expires after 15 minutes.
            $reset_token_expires_at = date(
                'Y-m-d H:i:s',
                time() + (15 * 60)
            );

            $update = get_db()->prepare(
                'UPDATE store_owner
                 SET reset_token_hash = ?,
                     reset_token_expires_at = ?
                 WHERE id = ?'
            );

            $update->execute([
                $reset_token_hash,
                $reset_token_expires_at,
                $owner['id']
            ]);

            // Create the reset link.
            $reset_link =
                rtrim(BASE_URL, '/') .
                '/reset_password.php?token=' .
                urlencode($reset_token);

            // Send the email.
            send_password_reset_email(
                $owner['email'],
                $owner['username'],
                $reset_link
            );
        }

        // Generic message for security.
        $message =
            'If an account matches the information provided, ' .
            'a password reset link has been sent to the registered email address.';
    }
}

$pageTitle = 'Forgot Password';

require __DIR__ . '/includes/header.php';
?>

<div class="login-wrap">

    <img
        src="assets/images/omar-logo.webp"
        alt="<?= e(STORE_NAME) ?>"
        class="login-logo"
    >

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card">

        <h2>Forgot Password?</h2>

        <p>
            Enter your username or registered email address.
            We'll send you a link to reset your password.
        </p>

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <div class="field">

            <label for="identifier">
                Username or Email
            </label>

            <input
                type="text"
                id="identifier"
                name="identifier"
                value="<?= e($_POST['identifier'] ?? '') ?>"
                placeholder="Enter username or email"
                autocomplete="username"
                required
                autofocus
            >

        </div>

        <button
            type="submit"
            class="btn btn-primary btn-block"
        >
            Send Reset Link
        </button>

        <a
            href="login.php"
            class="forgot-password"
        >
            Back to Login
        </a>

    </form>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
