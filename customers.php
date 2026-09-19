<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$search = trim($_GET['q'] ?? '');
$customers = get_all_customers($search);

$pageTitle = 'Customers';
require __DIR__ . '/includes/header.php';
?>

<a href="index.php" class="back-link">&larr; Dashboard</a>
<h1>Customers</h1>

<form method="get" class="search-box">
  <input type="text" name="q" placeholder="Search by name or phone" value="<?= e($search) ?>">
</form>

<a href="customer_add.php" class="btn btn-primary btn-block" style="margin-bottom:14px;">+ Add customer</a>

<div class="card" style="padding:0;">
  <?php if (empty($customers)): ?>
    <p class="empty-state">
      <?= $search !== '' ? 'No customers match that search.' : 'No customers yet. Add your first one above.' ?>
    </p>
  <?php else: ?>
    <ul class="customer-list">
      <?php foreach ($customers as $c): ?>
        <?php $balance = (float) $c['current_balance']; ?>
        <li>
          <a class="customer-row" href="customer_view.php?id=<?= (int) $c['id'] ?>">
            <span>
              <div class="name"><?= e($c['name']) ?></div>
              <?php if ($c['phone']): ?><div class="phone"><?= e($c['phone']) ?></div><?php endif; ?>
            </span>
            <span class="balance <?= $balance > 0 ? 'owing' : 'clear' ?>">
              <?= format_money($balance) ?>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
