<?php
// ============================================================
// Database Configuration
// ============================================================
// Edit these values to match your local / hosting environment.
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'budget_app_db');
define('DB_USER',     'root');          // change to your MySQL username
define('DB_PASS',     '');              // change to your MySQL password
define('DB_CHARSET',  'utf8mb4');

// ── Site base URL (no trailing slash) ────────────────────────
define('BASE_URL', 'http://localhost/budget-app');

// ── Session lifetime in seconds (default 2 hours) ────────────
define('SESSION_LIFETIME', 7200);

// ── Create PDO connection ─────────────────────────────────────
function getDBConnection(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
             <h2>Database Connection Error</h2>
             <p>' . htmlspecialchars($e->getMessage()) . '</p>
             <p>Please check your settings in <code>config/database.php</code>.</p>
             </div>');
    }

    return $pdo;
}
