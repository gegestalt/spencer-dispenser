// src/database.php
<?php

use PDO;

function getDatabaseConnection(): PDO {
    $dbFile = __DIR__ . '/../database/chat-app.db';
    echo "DB Path: " . $dbFile; 
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

