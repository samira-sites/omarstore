<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate fields
    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } elseif ($current_password === $new_password) {
        $error = 'Your new password must be different from your current password.';
    } else {
        // Get the currently logged-in owner's password hash
        $stmt = get_db()->prepare(
            'SELECT password_hash FROM store_owner WHERE id = ?'
        );
        $stmt->execute([$_SESSION['owner_id']]);
        $owner = $stmt->fetch();

        if (!$owner) {
            $error = 'Account could not be found.';
        } elseif (!password_verify($current_password, $owner['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Create a new secure password hash
            $new_password_hash = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            // Update the password
            $stmt = get_db()->prepare(
                'UPDATE store_owner
                 SET password_hash = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $new_password_hash,
                $_SESSION['owner_id']
            ]);

            $success = 'Your password has been changed successfully.';
        }
    }
}

$pageTitle = 'Change Password';
require __DIR__ . '/includes/header.php';
?>

<div class="login-wrap">

  <a href="index.php" class="back-link">&larr; Dashboard</a>

  <h1>Change Password</h1>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <?= e($success) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="card">

    <input
      type="hidden"
      name="csrf_token"
      value="<?= e(csrf_token()) ?>"
    >

    <div class="field">
      <label for="current_password">Current Password</label>

      <div class="password-wrapper">
        <input
          type="password"
          id="current_password"
          name="current_password"
          required
          autocomplete="current-password"
        >

        <button
          type="button"
          class="password-toggle"
          onclick="togglePassword('current_password', this)"
          aria-label="Show password"
        >👁</button>
      </div>
    </div>

    <div class="field">
      <label for="new_password">New Password</label>

      <div class="password-wrapper">
        <input
          type="password"
          id="new_password"
          name="new_password"
          required
          minlength="8"
          autocomplete="new-password"
        >

        <button
          type="button"
          class="password-toggle"
          onclick="togglePassword('new_password', this)"
          aria-label="Show password"
        >👁</button>
      </div>

      <small class="field-help">
        Password must be at least 8 characters.
      </small>
    </div>

    <div class="field">
      <label for="confirm_password">Confirm New Password</label>

      <div class="password-wrapper">
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          required
          minlength="8"
          autocomplete="new-password"
        >

        <button
          type="button"
          class="password-toggle"
          onclick="togglePassword('confirm_password', this)"
          aria-label="Show password"
        >👁</button>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">
      Change Password
    </button>

  </form>

</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);

    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
        button.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        button.textContent = '👁';
        button.setAttribute('aria-label', 'Show password');
    }
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>

### Add it to your navigation

Wherever you have your dashboard/navigation links, add:

<a href="change_password.php">Change Password</a>

### What this page does

It verifies the **current password**:


password_verify($current_password, $owner['password_hash'])

Then creates a new secure hash:

password_hash($new_password, PASSWORD_DEFAULT)

and saves it to the same `password_hash` column.

It also:

* Requires the user to be logged in.
* Uses your existing CSRF protection.
* Requires an 8-character minimum.
* Requires confirmation of the new password.
* Prevents using the same password again.
* Does **not** store passwords in plain text.
* Uses your existing password show/hide functionality.

**You do not need to change `auth.php` or your database table for this.**

After creating the file, test it while logged in by visiting `change_password.php`. Use your current password, create a new one, log out, and verify that the new password works.
