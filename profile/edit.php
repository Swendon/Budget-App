<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$user   = getUserById($userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name        = trim($_POST['name']        ?? '');
    $email       = trim($_POST['email']       ?? '');
    $password    = trim($_POST['password']    ?? '');
    $confirm     = trim($_POST['confirm']     ?? '');
    $currentPass = trim($_POST['current_password'] ?? '');

    if (empty($name))  $errors[] = 'Full name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if ($password !== '') {
        if (!password_verify($currentPass, $user['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($password) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'New passwords do not match.';
    }

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $chk  = $pdo->prepare('SELECT id FROM users WHERE email=? AND id != ?');
        $chk->execute([$email, $userId]);
        if ($chk->fetch()) {
            $errors[] = 'That email is already in use by another account.';
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, password=? WHERE id=?');
                $stmt->execute([$name, $email, $hash, $userId]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?');
                $stmt->execute([$name, $email, $userId]);
            }
            $_SESSION['user_name'] = $name;
            setFlash('success', 'Profile updated successfully.');
            redirect('profile/index.php');
        }
    }
}

$form = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $user;
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
  <a href="<?= BASE_URL ?>/profile/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card form-card">
  <div class="card-body">
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <h4>Basic Information</h4>
      <div class="form-group">
        <label for="name">Full Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($form['name']) ?>" required>
      </div>
      <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required>
      </div>

      <h4 style="margin-top:1.5rem">Change Password <small class="text-muted">(leave blank to keep current)</small></h4>
      <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" placeholder="Required to change password">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="password">New Password</label>
          <input type="password" id="password" name="password" placeholder="Min. 6 characters">
        </div>
        <div class="form-group">
          <label for="confirm">Confirm New Password</label>
          <input type="password" id="confirm" name="confirm" placeholder="Repeat new password">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        <a href="<?= BASE_URL ?>/profile/index.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
