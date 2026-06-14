<?php
// ============================================================
// Shared helper functions
// ============================================================

require_once __DIR__ . '/../config/database.php';

// ── Session bootstrap ─────────────────────────────────────────
function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        session_start();
    }
}

// ── Auth helpers ──────────────────────────────────────────────
function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function currentUserId(): int
{
    startSession();
    return (int) ($_SESSION['user_id'] ?? 0);
}

function currentUserName(): string
{
    startSession();
    return $_SESSION['user_name'] ?? '';
}

// ── Redirect helper ───────────────────────────────────────────
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

// ── Flash messages ────────────────────────────────────────────
function setFlash(string $type, string $message): void
{
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    startSession();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ── Output helpers ────────────────────────────────────────────
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function money(float $amount): string
{
    return number_format($amount, 2);
}

// ── CSRF helpers ──────────────────────────────────────────────
function csrfToken(): string
{
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    startSession();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Invalid request (CSRF token mismatch).');
    }
}

// ── User queries ──────────────────────────────────────────────
function getUserById(int $id): ?array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// ── Category queries ──────────────────────────────────────────
function getCategoriesByUser(int $userId, string $type = ''): array
{
    $pdo = getDBConnection();
    if ($type !== '') {
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = ? AND type = ? ORDER BY name');
        $stmt->execute([$userId, $type]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY type, name');
        $stmt->execute([$userId]);
    }
    return $stmt->fetchAll();
}

function getCategoryById(int $id, int $userId): ?array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    return $stmt->fetch() ?: null;
}

// ── Transaction queries ───────────────────────────────────────
function getTransactionById(int $id, int $userId): ?array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT t.*, c.name AS category_name
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.id = ? AND t.user_id = ?'
    );
    $stmt->execute([$id, $userId]);
    return $stmt->fetch() ?: null;
}

function getTotalByType(int $userId, string $type): float
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND transaction_type = ?'
    );
    $stmt->execute([$userId, $type]);
    return (float) $stmt->fetchColumn();
}

function getRecentTransactions(int $userId, int $limit = 10): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT t.*, c.name AS category_name
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.user_id = ?
         ORDER BY t.date DESC, t.created_at DESC
         LIMIT ?'
    );
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function getMonthlyTotals(int $userId, int $year, int $month): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT transaction_type, COALESCE(SUM(amount), 0) AS total
         FROM transactions
         WHERE user_id = ? AND YEAR(date) = ? AND MONTH(date) = ?
         GROUP BY transaction_type'
    );
    $stmt->execute([$userId, $year, $month]);
    $rows   = $stmt->fetchAll();
    $result = ['income' => 0.0, 'expense' => 0.0];
    foreach ($rows as $row) {
        $result[$row['transaction_type']] = (float) $row['total'];
    }
    return $result;
}

function getCategoryTotals(int $userId, string $type, int $year, int $month): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT c.name, COALESCE(SUM(t.amount), 0) AS total
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.user_id = ? AND t.transaction_type = ?
           AND YEAR(t.date) = ? AND MONTH(t.date) = ?
         GROUP BY c.id, c.name
         ORDER BY total DESC'
    );
    $stmt->execute([$userId, $type, $year, $month]);
    return $stmt->fetchAll();
}

function getMonthlyBalanceTrend(int $userId, int $months = 6): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT
            DATE_FORMAT(date, '%Y-%m') AS period,
            SUM(CASE WHEN transaction_type='income'  THEN amount ELSE 0 END) AS income,
            SUM(CASE WHEN transaction_type='expense' THEN amount ELSE 0 END) AS expense
         FROM transactions
         WHERE user_id = ?
           AND date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
         GROUP BY period
         ORDER BY period ASC"
    );
    $stmt->execute([$userId, $months]);
    return $stmt->fetchAll();
}

// ── Seed default categories for a new user ────────────────────
function seedDefaultCategories(int $userId): void
{
    $pdo        = getDBConnection();
    $defaults   = [
        ['Salary',         'income'],
        ['Business',       'income'],
        ['Freelance',      'income'],
        ['Investment',     'income'],
        ['Other Income',   'income'],
        ['Food',           'expense'],
        ['Transport',      'expense'],
        ['Utilities',      'expense'],
        ['Entertainment',  'expense'],
        ['Healthcare',     'expense'],
        ['Education',      'expense'],
        ['Rent',           'expense'],
        ['Savings',        'expense'],
        ['Other Expense',  'expense'],
    ];
    $stmt = $pdo->prepare('INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)');
    foreach ($defaults as [$name, $type]) {
        $stmt->execute([$userId, $name, $type]);
    }
}

// ── Pagination helper ─────────────────────────────────────────
function paginate(int $total, int $perPage, int $current): array
{
    $pages = (int) ceil($total / $perPage);
    return [
        'total'   => $total,
        'perPage' => $perPage,
        'current' => max(1, min($current, $pages)),
        'pages'   => $pages,
        'offset'  => (max(1, min($current, $pages)) - 1) * $perPage,
    ];
}
