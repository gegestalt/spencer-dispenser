<?php

use PDO;

function getDatabaseConnection(): PDO {
    $dbFile = __DIR__ . '/../database/chat-app.db';

    
    if (!file_exists($dbFile)) {
        throw new PDOException("Database file not found at: $dbFile");//verify db 
    }
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $db;
}
