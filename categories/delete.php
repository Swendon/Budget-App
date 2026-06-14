<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$id     = (int) ($_GET['id'] ?? 0);
$cat    = getCategoryById($id, $userId);

if (!$cat) {
    setFlash('error', 'Category not found.');
    redirect('categories/index.php');
}

$pdo   = getDBConnection();
$check = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE category_id = ? AND user_id = ?');
$check->execute([$id, $userId]);
$count = (int) $check->fetchColumn();

if ($count > 0) {
    setFlash('error', 'Cannot delete "' . $cat['name'] . '" — it has ' . $count . ' transaction(s) linked to it. Reassign or delete those first.');
    redirect('categories/index.php');
}

$stmt = $pdo->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
setFlash('success', 'Category "' . $cat['name'] . '" deleted.');
redirect('categories/index.php');
