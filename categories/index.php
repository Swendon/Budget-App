<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId     = currentUserId();
$categories = getCategoriesByUser($userId);
$income     = array_filter($categories, fn($c) => $c['type'] === 'income');
$expense    = array_filter($categories, fn($c) => $c['type'] === 'expense');

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-tags"></i> Categories</h2>
  <a href="<?= BASE_URL ?>/categories/create.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Add Category
  </a>
</div>

<div class="two-col-grid">
  <!-- Income categories -->
  <div class="card">
    <div class="card-header"><h3 class="text-income"><i class="fas fa-arrow-down"></i> Income Categories</h3></div>
    <div class="card-body">
      <?php if (empty($income)): ?>
        <div class="empty-state"><i class="fas fa-tags"></i><p>No income categories.</p></div>
      <?php else: ?>
      <table class="table">
        <thead><tr><th>Name</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($income as $cat): ?>
          <tr>
            <td><?= e($cat['name']) ?></td>
            <td class="text-center actions">
              <a href="<?= BASE_URL ?>/categories/edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
              <a href="<?= BASE_URL ?>/categories/delete.php?id=<?= $cat['id'] ?>"
                 class="btn btn-sm btn-danger confirm-delete"
                 data-name="<?= e($cat['name']) ?>"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Expense categories -->
  <div class="card">
    <div class="card-header"><h3 class="text-expense"><i class="fas fa-arrow-up"></i> Expense Categories</h3></div>
    <div class="card-body">
      <?php if (empty($expense)): ?>
        <div class="empty-state"><i class="fas fa-tags"></i><p>No expense categories.</p></div>
      <?php else: ?>
      <table class="table">
        <thead><tr><th>Name</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($expense as $cat): ?>
          <tr>
            <td><?= e($cat['name']) ?></td>
            <td class="text-center actions">
              <a href="<?= BASE_URL ?>/categories/edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
              <a href="<?= BASE_URL ?>/categories/delete.php?id=<?= $cat['id'] ?>"
                 class="btn btn-sm btn-danger confirm-delete"
                 data-name="<?= e($cat['name']) ?>"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
