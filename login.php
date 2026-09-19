<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (attempt_login($username, $password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Incorrect username or password.';
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>

<div class="login-wrap">

  <img src="assets/images/jehan-logo.webp"
       alt="<?= e(STORE_NAME) ?>"
       class="login-logo">

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>


  <form method="post" class="card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus required autocomplete="new-username">
    </div>
    <div class="field">
  <label for="password">Password</label>

  <div class="password-wrapper">
    <input type="password" id="password" name="password" required autocomplete="new-password">
    

    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Show password">
      👁
    </button>
  </div>
</div>

<button type="submit" class="btn btn-primary btn-block">Log in</button>
  </form>
</div>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    const toggle = document.querySelector('.password-toggle');

    if (password.type === 'password') {
        password.type = 'text';
        toggle.textContent = '🙈';
        toggle.setAttribute('aria-label', 'Hide password');
    } else {
        password.type = 'password';
        toggle.textContent = '👁';
        toggle.setAttribute('aria-label', 'Show password');
    }
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
