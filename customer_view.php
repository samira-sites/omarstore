<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$customer = get_customer($id);
if (!$customer) {
    header('Location: customers.php');
    exit;
}

$transactions = get_customer_transactions($id);

$pageTitle = $customer['name'];
require __DIR__ . '/includes/header.php';
?>

<a href="customers.php" class="back-link">&larr; Customers</a>

<h1><?= e($customer['name']) ?></h1>
<?php if ($customer['phone']): ?><p style="color:var(--muted);margin-top:-8px;"><?= e($customer['phone']) ?></p><?php endif; ?>

<div class="card">
  <div class="figure balance <?= (float) $customer['current_balance'] > 0 ? 'owing' : 'clear' ?>" style="font-size:1.8rem;">
    <?= format_money((float) $customer['current_balance']) ?>
  </div>
  <div class="label">Kasalukuyang Utang</div>
</div>

<div class="btn-row">
  <a href="charge_add.php?customer_id=<?= $id ?>" class="btn btn-rust">Mag Utang</a>
  <a href="payment_add.php?customer_id=<?= $id ?>" class="btn btn-primary">Mag Bayad</a>
</div>

<a href="statement.php?customer_id=<?= $id ?>" class="btn btn-outline btn-block" style="margin-bottom:20px;">View / print statement</a>

<h2>History</h2>
<div class="card" style="padding:0;">
  <?php if (empty($transactions)): ?>
    <p class="empty-state">No transactions yet.</p>
  <?php else: ?>
    <ul class="tx-list">
      <?php foreach ($transactions as $t): ?>
        <li>
          <div class="tx-item">
            <div>
              <div class="tx-desc">
                <?= $t['type'] === 'charge' ? 'Charge' : 'Payment' ?>
                <?php if ($t['items_description']): ?> &mdash; <?= e($t['items_description']) ?><?php endif; ?>
              </div>
              <div class="tx-date"><?= date('d M Y, g:ia', strtotime($t['created_at'])) ?></div>
            </div>
            <div>
              <div class="tx-amount <?= $t['type'] ?>">
                <?= $t['type'] === 'charge' ? '+' : '-' ?><?= format_money((float) $t['amount']) ?>
              </div>
              <div class="tx-running">bal: <?= format_money((float) $t['balance_after']) ?></div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
