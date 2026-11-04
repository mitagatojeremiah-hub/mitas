<?php
declare(strict_types=1);

final class MitasPDO extends PDO
{
    private string $mitasDriver;

    public function __construct(string $dsn, ?string $username = null, ?string $password = null, array $options = [])
    {
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        parent::__construct($dsn, $username, $password, $options);
        $this->mitasDriver = (string)$this->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function exec(string $statement): int|false
    {
        if ($this->mitasDriver === 'pgsql' && preg_match('/^\s*PRAGMA\s+/i', $statement)) {
            return 0;
        }
        return parent::exec($this->normalizeSql($statement));
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return parent::prepare($this->normalizeSql($query), $options);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $query = $this->normalizeSql($query);
        if ($fetchMode === null) {
            return parent::query($query);
        }
        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }

    public function lastInsertId(?string $name = null): string|false
    {
        if ($this->mitasDriver === 'pgsql' && $name === null) {
            $stmt = parent::query('SELECT LASTVAL()');
            return $stmt ? (string)$stmt->fetchColumn() : false;
        }
        return parent::lastInsertId($name);
    }

    private function normalizeSql(string $sql): string
    {
        if ($this->mitasDriver !== 'pgsql') {
            return $sql;
        }

        if (preg_match('/^\s*PRAGMA\s+table_info\s*\(\s*([A-Za-z_][A-Za-z0-9_]*)\s*\)\s*;?\s*$/i', $sql, $matches)) {
            $table = strtolower($matches[1]);
            return "SELECT column_name AS name FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = '{$table}' ORDER BY ordinal_position";
        }
        if (preg_match('/^\s*PRAGMA\s+/i', $sql)) {
            return 'SELECT 1 AS name WHERE FALSE';
        }

        $sql = preg_replace('/\bINTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT\b/i', 'BIGSERIAL PRIMARY KEY', $sql);
        $sql = preg_replace('/\b(created_at|updated_at|attempt_time|email_verified_at|expires_at|consumed_at)\s+TEXT\b/i', '$1 TIMESTAMP WITHOUT TIME ZONE', $sql);
        $sql = preg_replace('/\bTEXT(\s+NOT NULL)?\s+DEFAULT\s+CURRENT_TIMESTAMP\b/i', 'TEXT$1 DEFAULT CURRENT_TIMESTAMP::text', $sql);
        $sql = preg_replace('/datetime\s*\(\s*[\'\"]now[\'\"]\s*,\s*[\'\"]([+-]?\d+)\s+(seconds?|minutes?|hours?|days?)[\'\"]\s*\)/i', static function (array $matches): string {
            $amount = (int)$matches[1];
            $operator = $amount < 0 ? '-' : '+';
            return "CURRENT_TIMESTAMP {$operator} INTERVAL '" . abs($amount) . ' ' . strtolower($matches[2]) . "'";
        }, $sql);
        $sql = preg_replace('/datetime\s*\(\s*[\'\"]now[\'\"]\s*\)/i', 'CURRENT_TIMESTAMP', $sql);
        $sql = preg_replace('/date\s*\(\s*[\'\"]now[\'\"]\s*\)/i', 'CURRENT_DATE', $sql);
        $sql = preg_replace('/date\s*\(\s*([^()]+?)\s*\)/i', 'CAST($1 AS DATE)', $sql);
        return $sql;
    }
}

function mitas_db(): MitasPDO
{
    static $connection = null;
    if ($connection instanceof MitasPDO) {
        return $connection;
    }

    $databaseUrl = trim((string)getenv('DATABASE_URL'));
    if ($databaseUrl !== '') {
        $parts = parse_url($databaseUrl);
        if (!$parts || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['postgres', 'postgresql'], true)) {
            throw new RuntimeException('DATABASE_URL must be a PostgreSQL connection URL.');
        }
        if (!extension_loaded('pdo_pgsql')) {
            throw new RuntimeException('The PDO PostgreSQL extension is required when DATABASE_URL is set.');
        }

        parse_str((string)($parts['query'] ?? ''), $query);
        $host = (string)($parts['host'] ?? '');
        $database = rawurldecode(ltrim((string)($parts['path'] ?? ''), '/'));
        $port = (int)($parts['port'] ?? 5432);
        $sslMode = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($query['sslmode'] ?? 'require'));
        $dsn = 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';sslmode=' . ($sslMode ?: 'require');
        $connection = new MitasPDO(
            $dsn,
            rawurldecode((string)($parts['user'] ?? '')),
            rawurldecode((string)($parts['pass'] ?? ''))
        );
        return $connection;
    }

    if (getenv('VERCEL') === '1') {
        throw new RuntimeException('Set DATABASE_URL to a persistent PostgreSQL database before using Mitas on Vercel.');
    }

    $sqlitePath = getenv('MITAS_SQLITE_PATH') ?: (__DIR__ . '/data/mitas_system.sqlite');
    $sqliteDir = dirname($sqlitePath);
    if (!is_dir($sqliteDir) && !mkdir($sqliteDir, 0775, true) && !is_dir($sqliteDir)) {
        throw new RuntimeException('Unable to create the local SQLite data directory.');
    }
    $connection = new MitasPDO('sqlite:' . $sqlitePath);
    return $connection;
}
