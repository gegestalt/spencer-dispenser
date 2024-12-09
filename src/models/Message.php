// src/models/Message.php
<?php

class Message {
    public static function send($db, $group_id, $user_id, $content) {
        $stmt = $db->prepare('INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :content)');
        $stmt->execute([
            'group_id' => $group_id,
            'user_id' => $user_id,
            'content' => $content,
        ]);
        return $db->lastInsertId();
    }

    public static function getByGroup($db, $group_id) {
        $stmt = $db->prepare('SELECT m.id, m.content, m.created_at, u.username
                              FROM messages m
                              JOIN users u ON m.user_id = u.id
                              WHERE m.group_id = :group_id');
        $stmt->execute(['group_id' => $group_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
