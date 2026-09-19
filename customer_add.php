<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $stmt = get_db()->prepare(
            'INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $phone ?: null, $address ?: null]);
        $newId = (int) get_db()->lastInsertId();
        header('Location: customer_view.php?id=' . $newId);
        exit;
    }
}

$pageTitle = 'Add customer';
require __DIR__ . '/includes/header.php';
?>

<a href="customers.php" class="back-link">&larr; Customers</a>
<h1>Add customer</h1>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <div class="field">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" required autofocus value="<?= e($_POST['name'] ?? '') ?>">
  </div>
  <div class="field">
    <label for="phone">Phone</label>
    <input type="tel" id="phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
  </div>
  <div class="field">
    <label for="address">Address (optional)</label>
    <textarea id="address" name="address"><?= e($_POST['address'] ?? '') ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Save customer</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
