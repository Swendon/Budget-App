<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$id     = (int) ($_GET['id'] ?? 0);
$txn    = getTransactionById($id, $userId);

if (!$txn || $txn['transaction_type'] !== 'income') {
    setFlash('error', 'Income record not found.');
    redirect('income/index.php');
}

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

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $stmt = $pdo->prepare(
            'UPDATE transactions SET category_id=?, amount=?, description=?, date=?
             WHERE id=? AND user_id=?'
        );
        $stmt->execute([$categoryId, (float)$amount, $description, $date, $id, $userId]);
        setFlash('success', 'Income record updated.');
        redirect('income/index.php');
    }
}

// Pre-populate form
$form = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $txn;

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-edit text-income"></i> Edit Income</h2>
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
                 value="<?= e($form['amount']) ?>" required>
        </div>
        <div class="form-group">
          <label for="date">Date <span class="required">*</span></label>
          <input type="date" id="date" name="date" value="<?= e($form['date']) ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label for="category_id">Category <span class="required">*</span></label>
        <select id="category_id" name="category_id" required>
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"
              <?= $form['category_id'] == $cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?= e($form['description'] ?? '') ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update Income</button>
        <a href="<?= BASE_URL ?>/income/index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
