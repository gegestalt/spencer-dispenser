<?php

use PDO;

function getDatabaseConnection(): PDO {
    $dbFile = __DIR__ . '/../database/chat-app.db';

    //verify db 
    if (!file_exists($dbFile)) {
        throw new PDOException("Database file not found at: $dbFile");
    }

    // pdo instance for sqlite
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $db;
}
