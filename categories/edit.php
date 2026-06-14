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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');

    if (empty($name))                           $errors[] = 'Category name is required.';
    if (!in_array($type, ['income','expense']))  $errors[] = 'Please select a valid type.';

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $stmt = $pdo->prepare('UPDATE categories SET name=?, type=? WHERE id=? AND user_id=?');
        $stmt->execute([$name, $type, $id, $userId]);
        setFlash('success', 'Category updated.');
        redirect('categories/index.php');
    }
}

$form = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $cat;
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-edit"></i> Edit Category</h2>
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
          <option value="income"  <?= $form['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
          <option value="expense" <?= $form['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
      </div>

      <div class="form-group">
        <label for="name">Category Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($form['name']) ?>" required>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update Category</button>
        <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
