<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId     = currentUserId();
$categories = getCategoriesByUser($userId, 'income');
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $amount      = trim($_POST['amount']      ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['date']        ?? '');
    $categoryId  = (int) ($_POST['category_id'] ?? 0);

    if (!is_numeric($amount) || (float)$amount <= 0) $errors[] = 'Amount must be a positive number.';
    if (empty($date))                                 $errors[] = 'Date is required.';
    if ($categoryId <= 0)                             $errors[] = 'Please select a category.';
    if (!empty($date) && !strtotime($date))           $errors[] = 'Invalid date format.';

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO transactions (user_id, category_id, transaction_type, amount, description, date)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $categoryId, 'income', (float)$amount, $description, $date]);
        setFlash('success', 'Income record added successfully.');
        redirect('income/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-plus-circle text-income"></i> Add Income</h2>
  <a href="<?= BASE_URL ?>/income/index.php" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</div>

<div class="card form-card">
  <div class="card-body">
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-row">
        <div class="form-group">
          <label for="amount">Amount ($) <span class="required">*</span></label>
          <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                 value="<?= e($_POST['amount'] ?? '') ?>"
                 placeholder="0.00" required>
        </div>
        <div class="form-group">
          <label for="date">Date <span class="required">*</span></label>
          <input type="date" id="date" name="date"
                 value="<?= e($_POST['date'] ?? date('Y-m-d')) ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label for="category_id">Category <span class="required">*</span></label>
        <select id="category_id" name="category_id" required>
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"
              <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($categories)): ?>
          <small><a href="<?= BASE_URL ?>/categories/create.php?type=income">Create an income category first</a></small>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"
                  placeholder="Optional notes about this income..."><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Income</button>
        <a href="<?= BASE_URL ?>/income/index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
