<?php
/**
 * Database connection (PDO + SQLite).
 *
 * The first time the app runs, the database file is created
 * from database/schema.sql, so no manual import is needed.
 */

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    if (!extension_loaded('pdo_sqlite')) {
        http_response_code(500);
        exit('<h2>UniBus setup</h2><p>The PHP <b>pdo_sqlite</b> extension is not enabled.</p>'
            . '<p>Open your <code>php.ini</code>, remove the <code>;</code> in front of '
            . '<code>extension=pdo_sqlite</code>, save, and restart the server.</p>');
    }

    $isNew = !file_exists(DB_FILE);

    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        $pdo->exec(file_get_contents(DB_SCHEMA));
    }

    return $pdo;
}

/** Run a prepared query and return the statement. */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Fetch one row (or null). */
function q_one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** Fetch all rows. */
function q_all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/** Fetch a single value. */
function q_val(string $sql, array $params = [])
{
    return q($sql, $params)->fetchColumn();
}
