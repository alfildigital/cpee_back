<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    private function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_DATABASE') ?: 'cpee_db';
        $user = getenv('DB_USERNAME') ?: 'postgres';
        $pass = getenv('DB_PASSWORD') ?: 'postgres';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Ensure real prepared statements
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Log error internally, don't expose to user
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection error. Please contact administrator.");
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot serialize a singleton");
    }
}
