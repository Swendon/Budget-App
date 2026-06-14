<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
startSession();

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (empty($name))                          $errors[] = 'Full name is required.';
    if (empty($email))                         $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (strlen($password) < 6)                 $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $chk  = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $ins->execute([$name, $email, $hash]);
            $newId = (int) $pdo->lastInsertId();
            seedDefaultCategories($newId);
            setFlash('success', 'Account created! Please sign in.');
            redirect('auth/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — BudgetManager</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-page">
<div class="auth-card">
  <div class="auth-logo"><i class="fas fa-wallet"></i><h1>BudgetManager</h1></div>
  <h2>Create Account</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="form-group">
      <label for="name"><i class="fas fa-user"></i> Full Name</label>
      <input type="text" id="name" name="name"
             value="<?= e($_POST['name'] ?? '') ?>"
             placeholder="John Doe" required>
    </div>

    <div class="form-group">
      <label for="email"><i class="fas fa-envelope"></i> Email</label>
      <input type="email" id="email" name="email"
             value="<?= e($_POST['email'] ?? '') ?>"
             placeholder="you@example.com" required>
    </div>

    <div class="form-group">
      <label for="password"><i class="fas fa-lock"></i> Password</label>
      <input type="password" id="password" name="password"
             placeholder="Min. 6 characters" required>
    </div>

    <div class="form-group">
      <label for="confirm"><i class="fas fa-lock"></i> Confirm Password</label>
      <input type="password" id="confirm" name="confirm"
             placeholder="Repeat password" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
  </form>

  <p class="auth-link">Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Sign In</a></p>
</div>
</body>
</html>
