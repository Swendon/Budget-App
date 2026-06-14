<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId  = currentUserId();
$pdo     = getDBConnection();
$perPage = 15;
$page    = max(1, (int) ($_GET['page'] ?? 1));

$search   = trim($_GET['search']   ?? '');
$catId    = (int) ($_GET['category'] ?? 0);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';

$where  = ['t.user_id = ?', "t.transaction_type = 'income'"];
$params = [$userId];

if ($search !== '') {
    $where[]  = 't.description LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($catId > 0) {
    $where[]  = 't.category_id = ?';
    $params[] = $catId;
}
if ($dateFrom !== '') {
    $where[]  = 't.date >= ?';
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $where[]  = 't.date <= ?';
    $params[] = $dateTo;
}

$whereStr = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t WHERE $whereStr");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pg     = paginate($total, $perPage, $page);
$params2 = array_merge($params, [$perPage, $pg['offset']]);
$stmt    = $pdo->prepare(
    "SELECT t.*, c.name AS category_name
     FROM transactions t
     JOIN categories c ON c.id = t.category_id
     WHERE $whereStr
     ORDER BY t.date DESC, t.created_at DESC
     LIMIT ? OFFSET ?"
);
$stmt->execute($params2);
$records = $stmt->fetchAll();

$categories = getCategoriesByUser($userId, 'income');

// Monthly total for current filter context
$sumStmt = $pdo->prepare("SELECT COALESCE(SUM(t.amount),0) FROM transactions t WHERE $whereStr");
$sumStmt->execute($params);
$filteredTotal = (float) $sumStmt->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-arrow-down text-income"></i> Income Records</h2>
  <a href="<?= BASE_URL ?>/income/create.php" class="btn btn-success">
    <i class="fas fa-plus"></i> Add Income
  </a>
</div>

<!-- Filters -->
<div class="card filter-card">
  <form method="GET" class="filter-form">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search description...">
    <select name="category">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $catId == $cat['id'] ? 'selected' : '' ?>>
          <?= e($cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" value="<?= e($dateFrom) ?>" placeholder="From">
    <input type="date" name="date_to"   value="<?= e($dateTo) ?>"   placeholder="To">
    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
    <a href="<?= BASE_URL ?>/income/index.php" class="btn btn-secondary">Clear</a>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <span><?= $total ?> record<?= $total !== 1 ? 's' : '' ?> found</span>
    <span class="text-income"><strong>Total: Ksh <?= money($filteredTotal) ?></strong></span>
  </div>
  <div class="card-body">
    <?php if (empty($records)): ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <p>No income records found. <a href="<?= BASE_URL ?>/income/create.php">Add one now</a>.</p>
      </div>
    <?php else: ?>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Date</th>
          <th>Category</th>
          <th>Description</th>
          <th class="text-right">Amount</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($records as $r): ?>
        <tr>
          <td><?= e(date('d M Y', strtotime($r['date']))) ?></td>
          <td><span class="badge badge-income"><?= e($r['category_name']) ?></span></td>
          <td><?= e($r['description'] ?? '—') ?></td>
          <td class="text-right text-income"><strong>Ksh <?= money((float)$r['amount']) ?></strong></td>
          <td class="text-center actions">
            <a href="<?= BASE_URL ?>/income/edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <a href="<?= BASE_URL ?>/income/delete.php?id=<?= $r['id'] ?>"
               class="btn btn-sm btn-danger confirm-delete" title="Delete"
               data-name="<?= e($r['description'] ?? 'this record') ?>">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($pg['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $catId ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>"
           class="page-link <?= $i === $pg['current'] ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
