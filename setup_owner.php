<?php
/**
 * One-time setup: creates (or resets) the store owner login.
 * Run this once in your browser after uploading, then DELETE this file.
 */
require_once __DIR__ . '/includes/db.php';

$message = '';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($password) < 6) {
        $message = 'Username is required and password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db = get_db();
        $db->prepare('DELETE FROM store_owner')->execute();
        $stmt = $db->prepare('INSERT INTO store_owner (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrap">
  <h1>Setup login</h1>
  <?php if ($done): ?>
    <div class="alert alert-success">
      Login created. <a href="login.php">Go to login</a> — then delete <code>setup_owner.php</code> from the server.
    </div>
  <?php else: ?>
    <?php if ($message): ?><div class="alert alert-error"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post" class="card">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="field">
        <label for="password">Password (min 6 characters)</label>
        <input type="password" id="password" name="password" required minlength="6">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create login</button>
    </form>
    <p class="center-note">This replaces any existing login. Delete this file once done.</p>
  <?php endif; ?>
</div>
</body>
</html>
