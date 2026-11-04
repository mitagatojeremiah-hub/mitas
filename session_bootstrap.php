<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

final class MitasDatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private MitasPDO $pdo;
    private int $expirySeconds;

    public function __construct(MitasPDO $pdo)
    {
        $this->pdo = $pdo;
        $this->expirySeconds = max(60, (int)ini_get('session.gc_maxlifetime'));
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare('SELECT session_data FROM mitas_sessions WHERE id = ? AND last_activity >= ?');
        $stmt->execute([$id, time() - $this->expirySeconds]);
        $value = $stmt->fetchColumn();
        return $value === false ? '' : (string)$value;
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mitas_sessions (id, session_data, last_activity) VALUES (?, ?, ?)
             ON CONFLICT (id) DO UPDATE SET session_data = excluded.session_data, last_activity = excluded.last_activity'
        );
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM mitas_sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM mitas_sessions WHERE last_activity < ?');
        $stmt->execute([time() - max(60, $max_lifetime)]);
        return $stmt->rowCount();
    }

    public function validateId(string $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM mitas_sessions WHERE id = ? AND last_activity >= ?');
        $stmt->execute([$id, time() - $this->expirySeconds]);
        return (bool)$stmt->fetchColumn();
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE mitas_sessions SET last_activity = ? WHERE id = ?');
        $stmt->execute([time(), $id]);
        return $stmt->rowCount() > 0 || $this->write($id, $data);
    }
}

function mitas_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (
        (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || getenv('VERCEL') === '1'
    ) {
        ini_set('session.cookie_secure', '1');
    }

    $pdo = mitas_db();
    $pdo->exec('CREATE TABLE IF NOT EXISTS mitas_sessions (
        id TEXT PRIMARY KEY,
        session_data TEXT NOT NULL,
        last_activity BIGINT NOT NULL
    )');
    session_set_save_handler(new MitasDatabaseSessionHandler($pdo), true);
    session_start();
}
