<?php
require_once __DIR__ . '/db.php';

function format_money(float $amount): string
{
    return '₱' . number_format($amount, 2);
}

function get_customer(int $id): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM customers WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_all_customers(string $search = ''): array
{
    $db = get_db();
    if ($search !== '') {
        $stmt = $db->prepare(
            'SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY current_balance DESC, name ASC'
        );
        $like = '%' . $search . '%';
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $db->query('SELECT * FROM customers ORDER BY current_balance DESC, name ASC');
    }
    return $stmt->fetchAll();
}

function get_customer_transactions(int $customerId): array
{
    $stmt = get_db()->prepare(
        'SELECT * FROM credit_transactions WHERE customer_id = ? ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([$customerId]);
    return $stmt->fetchAll();
}

/**
 * Adds a charge or payment for a customer inside a transaction, updates
 * their running balance, and records a balance_after snapshot on the row.
 */
function add_credit_transaction(int $customerId, string $type, float $amount, ?string $description = null): void
{
    if ($amount <= 0) {
        throw new InvalidArgumentException('Amount must be greater than zero.');
    }
    if (!in_array($type, ['charge', 'payment'], true)) {
        throw new InvalidArgumentException('Invalid transaction type.');
    }

    $db = get_db();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT current_balance, name FROM customers WHERE id = ? FOR UPDATE');
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch();
        if (!$customer) {
            throw new RuntimeException('Customer not found.');
        }

        if ($type === 'payment' && $amount > (float) $customer['current_balance'] + 0.0005) {
            throw new InvalidArgumentException(
                'Sorry, sobra ang bayad. Ang balance ni ' . $customer['name'] . ' ay '
                . format_money((float) $customer['current_balance']) . ' lang.'
            );
        }

        $delta = $type === 'charge' ? $amount : -$amount;
        $newBalance = round((float) $customer['current_balance'] + $delta, 2);

        $insert = $db->prepare(
            'INSERT INTO credit_transactions (customer_id, type, amount, items_description, balance_after)
             VALUES (?, ?, ?, ?, ?)'
        );
        $insert->execute([$customerId, $type, $amount, $description, $newBalance]);

        $update = $db->prepare('UPDATE customers SET current_balance = ? WHERE id = ?');
        $update->execute([$newBalance, $customerId]);

        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function get_dashboard_summary(): array
{
    $db = get_db();

    $totalOutstanding = (float) $db->query(
        'SELECT COALESCE(SUM(current_balance), 0) FROM customers WHERE current_balance > 0'
    )->fetchColumn();

    $unpaidCount = (int) $db->query(
        'SELECT COUNT(*) FROM customers WHERE current_balance > 0'
    )->fetchColumn();

    $topBalances = $db->query(
        'SELECT id, name, phone, current_balance FROM customers
         WHERE current_balance > 0
         ORDER BY current_balance DESC
         LIMIT 5'
    )->fetchAll();

    return [
        'total_outstanding' => $totalOutstanding,
        'unpaid_count' => $unpaidCount,
        'top_balances' => $topBalances,
    ];
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
