<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$errors = [];
$preType = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');

    if (empty($name))                         $errors[] = 'Category name is required.';
    if (!in_array($type, ['income','expense'])) $errors[] = 'Please select a valid type.';

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $chk  = $pdo->prepare('SELECT id FROM categories WHERE user_id=? AND name=? AND type=?');
        $chk->execute([$userId, $name, $type]);
        if ($chk->fetch()) {
            $errors[] = 'A ' . $type . ' category with that name already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (user_id, name, type) VALUES (?,?,?)');
            $stmt->execute([$userId, $name, $type]);
            setFlash('success', 'Category "' . $name . '" created.');
            redirect('categories/index.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-plus-circle"></i> Add Category</h2>
  <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card form-card">
  <div class="card-body">
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-group">
        <label for="type">Type <span class="required">*</span></label>
        <select id="type" name="type" required>
          <option value="">-- Select Type --</option>
          <option value="income"  <?= (($_POST['type'] ?? $preType) === 'income')  ? 'selected' : '' ?>>Income</option>
          <option value="expense" <?= (($_POST['type'] ?? $preType) === 'expense') ? 'selected' : '' ?>>Expense</option>
        </select>
      </div>

      <div class="form-group">
        <label for="name">Category Name <span class="required">*</span></label>
        <input type="text" id="name" name="name"
               value="<?= e($_POST['name'] ?? '') ?>"
               placeholder="e.g. Grocery, Salary, Rent" required>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
        <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
