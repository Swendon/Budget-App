<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$id     = (int) ($_GET['id'] ?? 0);
$txn    = getTransactionById($id, $userId);

if (!$txn || $txn['transaction_type'] !== 'expense') {
    setFlash('error', 'Expense record not found.');
    redirect('expenses/index.php');
}

$pdo  = getDBConnection();
$stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);

setFlash('success', 'Expense deleted.');
redirect('expenses/index.php');
