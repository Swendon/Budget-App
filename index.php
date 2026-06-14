<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$userId = currentUserId();
$now    = new DateTime();
$year   = (int) $now->format('Y');
$month  = (int) $now->format('n');

$totalIncome  = getTotalByType($userId, 'income');
$totalExpense = getTotalByType($userId, 'expense');
$balance      = $totalIncome - $totalExpense;

$monthly     = getMonthlyTotals($userId, $year, $month);
$monthIncome = $monthly['income'];
$monthExpense= $monthly['expense'];
$monthBalance= $monthIncome - $monthExpense;

$recent = getRecentTransactions($userId, 8);
$trend  = getMonthlyBalanceTrend($userId, 6);

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
  <span class="subtitle"><?= date('F Y') ?></span>
</div>

<!-- ── Summary Cards ─────────────────────────────────────── -->
<div class="stats-grid">
  <div class="stat-card stat-income">
    <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
    <div class="stat-body">
      <span class="stat-label">Total Income</span>
      <span class="stat-value">Ksh <?= money($totalIncome) ?></span>
    </div>
  </div>
  <div class="stat-card stat-expense">
    <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
    <div class="stat-body">
      <span class="stat-label">Total Expenses</span>
      <span class="stat-value">Ksh <?= money($totalExpense) ?></span>
    </div>
  </div>
  <div class="stat-card stat-balance <?= $balance >= 0 ? 'positive' : 'negative' ?>">
    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
    <div class="stat-body">
      <span class="stat-label">Current Balance</span>
      <span class="stat-value"><?= $balance < 0 ? '-' : '' ?>Ksh <?= money(abs($balance)) ?></span>
    </div>
  </div>
  <div class="stat-card stat-month">
    <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
    <div class="stat-body">
      <span class="stat-label">This Month Balance</span>
      <span class="stat-value <?= $monthBalance >= 0 ? 'text-income' : 'text-expense' ?>">
        <?= $monthBalance < 0 ? '-' : '' ?>$<?= money(abs($monthBalance)) ?>
      </span>
    </div>
  </div>
</div>

<!-- ── This Month Progress ────────────────────────────────── -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-chart-pie"></i> <?= date('F Y') ?> Overview</h3>
  </div>
  <div class="card-body month-overview">
    <div class="month-item">
      <span class="month-label">Income</span>
      <div class="progress-bar">
        <div class="progress-fill progress-income" style="width:<?= $monthIncome > 0 ? 100 : 0 ?>%"></div>
      </div>
      <span class="month-amount text-income">Ksh <?= money($monthIncome) ?></span>
    </div>
    <?php
    $expPct = $monthIncome > 0 ? min(100, round($monthExpense / $monthIncome * 100)) : ($monthExpense > 0 ? 100 : 0);
    ?>
    <div class="month-item">
      <span class="month-label">Expenses</span>
      <div class="progress-bar">
        <div class="progress-fill progress-expense" style="width:<?= $expPct ?>%"></div>
      </div>
      <span class="month-amount text-expense">Ksh <?= money($monthExpense) ?> (<?= $expPct ?>%)</span>
    </div>
  </div>
</div>

<!-- ── Recent Transactions ────────────────────────────────── -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-history"></i> Recent Transactions</h3>
    <div>
      <a href="<?= BASE_URL ?>/income/create.php"   class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Income</a>
      <a href="<?= BASE_URL ?>/expenses/create.php" class="btn btn-sm btn-danger"><i class="fas fa-plus"></i> Expense</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($recent)): ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <p>No transactions yet. <a href="<?= BASE_URL ?>/income/create.php">Add your first income</a> or
           <a href="<?= BASE_URL ?>/expenses/create.php">record an expense</a>.</p>
      </div>
    <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Category</th>
          <th>Description</th>
          <th class="text-right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $txn): ?>
        <tr>
          <td><?= e(date('d M Y', strtotime($txn['date']))) ?></td>
          <td>
            <?php if ($txn['transaction_type'] === 'income'): ?>
              <span class="badge badge-income">Income</span>
            <?php else: ?>
              <span class="badge badge-expense">Expense</span>
            <?php endif; ?>
          </td>
          <td><?= e($txn['category_name']) ?></td>
          <td><?= e($txn['description'] ?? '—') ?></td>
          <td class="text-right <?= $txn['transaction_type'] === 'income' ? 'text-income' : 'text-expense' ?>">
            <?= $txn['transaction_type'] === 'income' ? '+' : '-' ?>Ksh <?= money((float)$txn['amount']) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- ── Balance Trend ──────────────────────────────────────── -->
<?php if (!empty($trend)): ?>
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-chart-line"></i> 6-Month Balance Trend</h3>
  </div>
  <div class="card-body">
    <div class="trend-table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Month</th>
            <th class="text-right">Income</th>
            <th class="text-right">Expenses</th>
            <th class="text-right">Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($trend as $row):
            $bal = (float)$row['income'] - (float)$row['expense'];
          ?>
          <tr>
            <td><?= e(date('M Y', strtotime($row['period'] . '-01'))) ?></td>
            <td class="text-right text-income">$<?= money((float)$row['income']) ?></td>
            <td class="text-right text-expense">$<?= money((float)$row['expense']) ?></td>
            <td class="text-right <?= $bal >= 0 ? 'text-income' : 'text-expense' ?>">
              <?= $bal < 0 ? '-' : '' ?>$<?= money(abs($bal)) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
