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

    if ($amount <= 0) {
      $error = 'Enter an amount greater than zero.';
  } else {
      try {
          add_credit_transaction($customerId, 'payment', $amount, null);
          header('Location: customer_view.php?id=' . $customerId);
          exit;
      } catch (InvalidArgumentException $e) {
          $error = $e->getMessage();
      }
  }
}

$pageTitle = 'Record payment';
require __DIR__ . '/includes/header.php';
?>

<a href="customer_view.php?id=<?= $customerId ?>" class="back-link">&larr; <?= e($customer['name']) ?></a>
<h1>Record payment</h1>
<p style="color:var(--muted);margin-top:-8px;">
  Current balance: <strong><?= format_money((float) $customer['current_balance']) ?></strong>
</p>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="customer_id" value="<?= $customerId ?>">
  <div class="field">
    <label for="amount">Amount paid</label>
    <input type="number" id="amount" name="amount" step="0.001" min="0.001" required autofocus inputmode="decimal"
           value="<?= (float) $customer['current_balance'] > 0 ? e(rtrim(rtrim(number_format((float) $customer['current_balance'], 3, '.', ''), '0'), '.')) : '' ?>">
  </div>
  <button type="submit" class="btn btn-primary btn-block">Record payment</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
