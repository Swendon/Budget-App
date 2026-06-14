<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
startSession();

$flash    = getFlash();
$loggedIn = isLoggedIn();
$userName = currentUserName();
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Budget Manager</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php if ($loggedIn): ?>
<nav class="navbar">
  <div class="nav-brand">
    <i class="fas fa-wallet"></i>
    <span>BudgetManager</span>
  </div>
  <button class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></button>
  <ul class="nav-links" id="navLinks">
    <li><a href="<?= BASE_URL ?>/index.php"
         class="<?= ($currentPage === 'index.php' && $currentDir === 'budget-app') || ($currentPage === 'index.php' && $currentDir === dirname(BASE_URL)) ? 'active' : '' ?>">
         <i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li><a href="<?= BASE_URL ?>/income/index.php"
         class="<?= $currentDir === 'income'  ? 'active' : '' ?>">
         <i class="fas fa-arrow-down text-income"></i> Income</a></li>
    <li><a href="<?= BASE_URL ?>/expenses/index.php"
         class="<?= $currentDir === 'expenses' ? 'active' : '' ?>">
         <i class="fas fa-arrow-up text-expense"></i> Expenses</a></li>
    <li><a href="<?= BASE_URL ?>/categories/index.php"
         class="<?= $currentDir === 'categories' ? 'active' : '' ?>">
         <i class="fas fa-tags"></i> Categories</a></li>
    <li><a href="<?= BASE_URL ?>/reports/index.php"
         class="<?= $currentDir === 'reports' ? 'active' : '' ?>">
         <i class="fas fa-chart-bar"></i> Reports</a></li>
    <li class="nav-user">
      <a href="<?= BASE_URL ?>/profile/index.php">
        <i class="fas fa-user-circle"></i> <?= e($userName) ?>
      </a>
    </li>
    <li><a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</nav>
<?php endif; ?>

<main class="main-content <?= $loggedIn ? 'with-nav' : '' ?>">

<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>" id="flashMsg">
    <?= e($flash['message']) ?>
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
<?php endif; ?>
