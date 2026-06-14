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

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email))    $errors[] = 'Email is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            session_regenerate_id(true);
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('index.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — BudgetManager</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-page">
<div class="auth-card">
  <div class="auth-logo"><i class="fas fa-wallet"></i><h1>BudgetManager</h1></div>
  <h2>Sign In</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="form-group">
      <label for="email"><i class="fas fa-envelope"></i> Email</label>
      <input type="email" id="email" name="email"
             value="<?= e($_POST['email'] ?? '') ?>"
             placeholder="you@example.com" required>
    </div>

    <div class="form-group">
      <label for="password"><i class="fas fa-lock"></i> Password</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
  </form>

  <p class="auth-link">Don't have an account? <a href="<?= BASE_URL ?>/auth/register.php">Register</a></p>
</div>
</body>
</html>
