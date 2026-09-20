<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$customerId = (int) ($_GET['customer_id'] ?? 0);
$customer = get_customer($customerId);
if (!$customer) {
    header('Location: customers.php');
    exit;
}

$transactions = get_customer_transactions($customerId);
$pageTitle = 'Statement — ' . $customer['name'];
require __DIR__ . '/includes/header.php';
?>

<a href="customer_view.php?id=<?= $customerId ?>" class="back-link no-print">&larr; <?= e($customer['name']) ?></a>

<h1><?= e(STORE_NAME) ?></h1>
<p style="margin-top:-8px;">Statement for <strong><?= e($customer['name']) ?></strong>
  <?php if ($customer['phone']): ?> &middot; <?= e($customer['phone']) ?><?php endif; ?>
</p>
<p style="color:var(--muted);">Printed <?= date('d M Y, g:ia') ?></p>

<div class="card">
  <div class="figure balance <?= (float) $customer['current_balance'] > 0 ? 'owing' : 'clear' ?>" style="font-size:1.8rem;">
    <?= format_money((float) $customer['current_balance']) ?>
  </div>
  <div class="label">Total owed</div>
</div>

<button onclick="window.print()" class="btn btn-primary view-print btn-block no-print " style="margin-bottom:20px;">Print statement</button>

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
