<?php
// config/database.php

// College server DB credentials (change only if your teacher gave different ones)
define('DB_HOST', 'localhost');
define('DB_NAME', 'np03cy4s250018');
define('DB_USER', 'np03cy4s250018');
define('DB_PASS', 'fhmkclqK6G');

function db() {
    static $pdo = null;

    if ($pdo === null) {
        // connect to database
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo "<h3>Database connection failed</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            exit;
        }
    }

    return $pdo;
}
