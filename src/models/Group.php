// src/models/Group.php
<?php

class Group {
    public static function create($db, $name) {
        $stmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
        return $db->lastInsertId();
    }

    public static function getAll($db) {
        $stmt = $db->query('SELECT * FROM groups');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
