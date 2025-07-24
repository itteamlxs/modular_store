<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME']
            );
            self::$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    public static function view(string $name, array $params = []): array
    {
        $sql = "SELECT * FROM {$name}";
        if ($params) {
            $where = implode(' AND ', array_map(fn($k) => "{$k} = :{$k}", array_keys($params)));
            $sql .= " WHERE {$where}";
        }
        
        // Debug
        if ($_ENV['APP_ENV'] === 'development') {
            error_log("Database query: $sql with params: " . json_encode($params));
        }
        
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        
        // Debug
        if ($_ENV['APP_ENV'] === 'development') {
            error_log("Database result count: " . count($result));
        }
        
        return $result;
    }
}