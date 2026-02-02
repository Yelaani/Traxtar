<?php

class Database {
    private static $pdo = null;

    // Preferred
    public static function pdo() {
        if (self::$pdo === null) {
            $cfg = include __DIR__ . '/config.php';

            $dsn = 'mysql:host='.$cfg['db']['host'].
                   ';port='.$cfg['db']['port'].
                   ';dbname='.$cfg['db']['name'].
                   ';charset='.$cfg['db']['charset'];

            try {
                self::$pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    // Backwards-compatible alias for older models
    public static function connect() { return self::pdo(); }

    public static function disconnect() { self::$pdo = null; }
}
