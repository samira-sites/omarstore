<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$customerId = (int) ($_GET['customer_id'] ?? $_POST['customer_id'] ?? 0);
$customer = get_customer($customerId);
if (!$customer) {
    header('Location: customers.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $amount = (float) ($_POST['amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($amount <= 0) {
        $error = 'Enter an amount greater than zero.';
    } else {
        add_credit_transaction($customerId, 'charge', $amount, $description ?: null);
        header('Location: customer_view.php?id=' . $customerId);
        exit;
    }
}

$pageTitle = 'Add charge';
require __DIR__ . '/includes/header.php';
?>

<a href="customer_view.php?id=<?= $customerId ?>" class="back-link">&larr; <?= e($customer['name']) ?></a>
<h1>Add charge</h1>
<p style="color:var(--muted);margin-top:-8px;">Logs a new purchase on <?= e($customer['name']) ?>'s tab.</p>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="customer_id" value="<?= $customerId ?>">
  <div class="field">
    <label for="amount">Amount</label>
    <input type="number" id="amount" name="amount" step="0.001" min="0.001" required autofocus inputmode="decimal">
  </div>
  <div class="field">
    <label for="description">Ano ang mga inutang? (optional)</label>
    <textarea id="description" name="description" placeholder="e.g. rice, eggs, milk"></textarea>
  </div>
  <button type="submit" class="btn btn-rust btn-block">Add charge</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
