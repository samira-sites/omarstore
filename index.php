<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$summary = get_dashboard_summary();
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<h1>Dashboard</h1>

<div class="summary-row">
  <div class="card">
    <div class="figure"><?= format_money($summary['total_outstanding']) ?></div>
    <div class="label">Total outstanding credit</div>
  </div>
  <div class="card">
    <div class="figure"><?= $summary['unpaid_count'] ?></div>
    <div class="label">Customers with a balance</div>
  </div>
</div>

<div class="btn-row">
  <a href="customers.php" class="btn btn-primary">View customers</a>
  <a href="customer_add.php" class="btn btn-outline">Add customer</a>
</div>

<h2>Highest balances</h2>
<div class="card">
  <?php if (empty($summary['top_balances'])): ?>
    <p class="empty-state">No outstanding balances right now.</p>
  <?php else: ?>
    <?php foreach ($summary['top_balances'] as $c): ?>
      <div class="top-balance-item">
        <a href="customer_view.php?id=<?= (int) $c['id'] ?>"><?= e($c['name']) ?></a>
        <span class="balance owing"><?= format_money((float) $c['current_balance']) ?></span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
