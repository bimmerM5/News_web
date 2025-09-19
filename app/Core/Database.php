<?php
namespace App\Core;

class Database
{
    private static ?\PDO $pdo = null;

    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../Config/config.php';
            $db = $config['db'];
            $dsn = 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'];
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            self::$pdo = new \PDO($dsn, $db['user'], $db['pass'], $options);
        }
        return self::$pdo;
    }
}
