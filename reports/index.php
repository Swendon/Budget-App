<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = currentUserId();
$now    = new DateTime();

$year  = (int) ($_GET['year']  ?? $now->format('Y'));
$month = (int) ($_GET['month'] ?? $now->format('n'));

if ($year < 2000 || $year > 2100) $year  = (int) $now->format('Y');
if ($month < 1   || $month > 12)  $month = (int) $now->format('n');

$monthly      = getMonthlyTotals($userId, $year, $month);
$incCats      = getCategoryTotals($userId, 'income',  $year, $month);
$expCats      = getCategoryTotals($userId, 'expense', $year, $month);
$trend        = getMonthlyBalanceTrend($userId, 12);

$monthIncome  = $monthly['income'];
$monthExpense = $monthly['expense'];
$monthBalance = $monthIncome - $monthExpense;

// All transactions for the selected month
$pdo    = getDBConnection();
$stmt   = $pdo->prepare(
    "SELECT t.*, c.name AS category_name
     FROM transactions t
     JOIN categories c ON c.id = t.category_id
     WHERE t.user_id=? AND YEAR(t.date)=? AND MONTH(t.date)=?
     ORDER BY t.date DESC"
);
$stmt->execute([$userId, $year, $month]);
$monthTxns = $stmt->fetchAll();

// Year list for selector
$yearStmt = $pdo->prepare('SELECT DISTINCT YEAR(date) AS y FROM transactions WHERE user_id=? ORDER BY y DESC');
$yearStmt->execute([$userId]);
$years = array_column($yearStmt->fetchAll(), 'y');
if (empty($years)) $years = [$year];

$monthNames = ['January','February','March','April','May','June',
               'July','August','September','October','November','December'];

include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
  <h2><i class="fas fa-chart-bar"></i> Reports</h2>
</div>

<!-- Period Selector -->
<div class="card filter-card">
  <form method="GET" class="filter-form">
    <select name="month">
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= $monthNames[$m-1] ?></option>
      <?php endfor; ?>
    </select>
    <select name="year">
      <?php foreach ($years as $y): ?>
        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> View Report</button>
  </form>
</div>

<!-- Month Summary -->
<div class="stats-grid">
  <div class="stat-card stat-income">
    <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
    <div class="stat-body">
      <span class="stat-label">Monthly Income</span>
      <span class="stat-value">Ksh <?= money($monthIncome) ?></span>
    </div>
  </div>
  <div class="stat-card stat-expense">
    <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
    <div class="stat-body">
      <span class="stat-label">Monthly Expenses</span>
      <span class="stat-value">Ksh <?= money($monthExpense) ?></span>
    </div>
  </div>
  <div class="stat-card stat-balance <?= $monthBalance >= 0 ? 'positive' : 'negative' ?>">
    <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
    <div class="stat-body">
      <span class="stat-label">Net Balance</span>
      <span class="stat-value"><?= $monthBalance < 0 ? '-' : '' ?>Ksh <?= money(abs($monthBalance)) ?></span>
    </div>
  </div>
</div>

<!-- Line Graph Report -->
<div class="card" style="max-width:640px; margin:0 auto 1.25rem;">
  <div class="card-header"><h3><i class="fas fa-chart-line"></i> Income & Expenses Trend</h3></div>
  <div class="card-body" style="padding:1rem 1rem 0;">
    <canvas id="trendChart" style="width:100%; height:180px;"></canvas>
  </div>
</div>

<!-- Category Analysis -->
<div class="two-col-grid">
  <div class="card">
    <div class="card-header"><h3 class="text-income"><i class="fas fa-chart-pie"></i> Income by Category</h3></div>
    <div class="card-body">
      <?php if (empty($incCats)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><p>No income this month.</p></div>
      <?php else: ?>
      <table class="table">
        <thead><tr><th>Category</th><th class="text-right">Amount</th><th class="text-right">Share</th></tr></thead>
        <tbody>
          <?php foreach ($incCats as $row):
            $pct = $monthIncome > 0 ? round((float)$row['total'] / $monthIncome * 100, 1) : 0;
          ?>
          <tr>
            <td><?= e($row['name']) ?></td>
            <td class="text-right text-income">Ksh <?= money((float)$row['total']) ?></td>
            <td class="text-right">
              <div class="mini-progress">
                <div class="mini-fill income-fill" style="width:<?= $pct ?>%"></div>
              </div>
              <?= $pct ?>%
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3 class="text-expense"><i class="fas fa-chart-pie"></i> Expenses by Category</h3></div>
    <div class="card-body">
      <?php if (empty($expCats)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><p>No expenses this month.</p></div>
      <?php else: ?>
      <table class="table">
        <thead><tr><th>Category</th><th class="text-right">Amount</th><th class="text-right">Share</th></tr></thead>
        <tbody>
          <?php foreach ($expCats as $row):
            $pct = $monthExpense > 0 ? round((float)$row['total'] / $monthExpense * 100, 1) : 0;
          ?>
          <tr>
            <td><?= e($row['name']) ?></td>
            <td class="text-right text-expense">Ksh <?= money((float)$row['total']) ?></td>
            <td class="text-right">
              <div class="mini-progress">
                <div class="mini-fill expense-fill" style="width:<?= $pct ?>%"></div>
              </div>
              <?= $pct ?>%
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- 12-Month Trend -->
<?php if (!empty($trend)): ?>
<div class="card">
  <div class="card-header"><h3><i class="fas fa-chart-line"></i> 12-Month Balance Trend</h3></div>
  <div class="card-body">
    <table class="table">
      <thead>
        <tr><th>Month</th><th class="text-right">Income</th><th class="text-right">Expenses</th><th class="text-right">Balance</th><th>Visual</th></tr>
      </thead>
      <tbody>
        <?php
        $maxInc = max(array_column($trend, 'income'));
        $maxExp = max(array_column($trend, 'expense'));
        $maxVal = max($maxInc, $maxExp, 1);
        foreach ($trend as $row):
          $bal    = (float)$row['income'] - (float)$row['expense'];
          $incPct = round((float)$row['income']  / $maxVal * 100);
          $expPct = round((float)$row['expense'] / $maxVal * 100);
        ?>
        <tr>
          <td><?= date('M Y', strtotime($row['period'].'-01')) ?></td>
          <td class="text-right text-income">Ksh <?= money((float)$row['income']) ?></td>
          <td class="text-right text-expense">Ksh <?= money((float)$row['expense']) ?></td>
          <td class="text-right <?= $bal >= 0 ? 'text-income' : 'text-expense' ?>">
            <?= $bal < 0 ? '-' : '' ?>Ksh <?= money(abs($bal)) ?>
          </td>
          <td style="width:160px">
            <div class="bar-row">
              <div class="bar-seg income-bar" style="width:<?= $incPct ?>%" title="Income"></div>
            </div>
            <div class="bar-row">
              <div class="bar-seg expense-bar" style="width:<?= $expPct ?>%" title="Expense"></div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Full Transaction List for Month -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-list"></i> All Transactions — <?= $monthNames[$month-1] ?> <?= $year ?></h3>
    <span><?= count($monthTxns) ?> transaction<?= count($monthTxns) !== 1 ? 's' : '' ?></span>
  </div>
  <div class="card-body">
    <?php if (empty($monthTxns)): ?>
      <div class="empty-state"><i class="fas fa-receipt"></i><p>No transactions for this period.</p></div>
    <?php else: ?>
    <table class="table">
      <thead>
        <tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th class="text-right">Amount</th></tr>
      </thead>
      <tbody>
        <?php foreach ($monthTxns as $txn): ?>
        <tr>
          <td><?= date('d M', strtotime($txn['date'])) ?></td>
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
            <strong><?= $txn['transaction_type'] === 'income' ? '+' : '-' ?>Ksh <?= money((float)$txn['amount']) ?></strong>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
  const trendLabels = <?= json_encode(array_map(fn($row) => date('M Y', strtotime($row['period'].'-01')), $trend)) ?>;
  const incomeData  = <?= json_encode(array_map(fn($row) => (float) $row['income'], $trend)) ?>;
  const expenseData = <?= json_encode(array_map(fn($row) => (float) $row['expense'], $trend)) ?>;

  const trendCtx = document.getElementById('trendChart');
  if (!trendCtx) return;

  new Chart(trendCtx, {
    type: 'line',
    data: {
      labels: trendLabels,
      datasets: [
        {
          label: 'Income',
          data: incomeData,
          borderColor: 'rgba(22,163,74,0.9)',
          backgroundColor: 'rgba(22,163,74,0.2)',
          fill: true,
          tension: 0.3,
          pointRadius: 2,
          pointHoverRadius: 4,
        },
        {
          label: 'Expenses',
          data: expenseData,
          borderColor: 'rgba(220,38,38,0.9)',
          backgroundColor: 'rgba(220,38,38,0.2)',
          fill: true,
          tension: 0.3,
          pointRadius: 2,
          pointHoverRadius: 4,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: { mode: 'index', intersect: false }
      },
      interaction: { mode: 'nearest', axis: 'x', intersect: false },
      scales: {
        x: { display: true },
        y: { display: true, beginAtZero: true }
      }
    }
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
