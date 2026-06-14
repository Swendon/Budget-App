<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$user   = getUserById($userId);

$pdo       = getDBConnection();
$countInc  = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id=? AND transaction_type='income'");
$countInc->execute([$userId]);
$countExp  = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id=? AND transaction_type='expense'");
$countExp->execute([$userId]);
$countCat  = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE user_id=?");
$countCat->execute([$userId]);

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-user-circle"></i> My Profile</h2>
  <a href="<?= BASE_URL ?>/profile/edit.php" class="btn btn-primary">
    <i class="fas fa-edit"></i> Edit Profile
  </a>
</div>

<div class="profile-grid">
  <div class="card profile-card">
    <div class="card-body text-center">
      <div class="avatar-circle"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
      <h3><?= e($user['name']) ?></h3>
      <p class="text-muted"><?= e($user['email']) ?></p>
      <p class="text-muted small">Member since <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Account Statistics</h3></div>
    <div class="card-body">
      <div class="stats-list">
        <div class="stat-row">
          <span><i class="fas fa-arrow-down text-income"></i> Income Records</span>
          <strong><?= $countInc->fetchColumn() ?></strong>
        </div>
        <div class="stat-row">
          <span><i class="fas fa-arrow-up text-expense"></i> Expense Records</span>
          <strong><?= $countExp->fetchColumn() ?></strong>
        </div>
        <div class="stat-row">
          <span><i class="fas fa-tags"></i> Categories</span>
          <strong><?= $countCat->fetchColumn() ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
