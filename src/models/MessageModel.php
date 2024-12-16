<?php

namespace App\Models;

use PDO;

class MessageModel {
    /**
     * Send a new message to the database.
     *
     * @param PDO $db The database connection.
     * @param int $group_id The ID of the group.
     * @param int $user_id The ID of the user.
     * @param string $content The message content.
     * @return int The ID of the newly created message.
     */
    public static function send(PDO $db, int $group_id, int $user_id, string $content): int {
        $stmt = $db->prepare('
            INSERT INTO messages (group_id, user_id, content) 
            VALUES (:group_id, :user_id, :content)
        ');
        $stmt->execute([
            'group_id' => $group_id,
            'user_id' => $user_id,
            'content' => $content,
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Retrieve all messages for a specific group.
     *
     * @param PDO $db The database connection.
     * @param int $group_id The ID of the group.
     * @return array The list of messages.
     */
    public static function getByGroup(PDO $db, int $group_id): array {
        $stmt = $db->prepare('
            SELECT m.id, m.content, m.created_at, u.username
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.group_id = :group_id
            ORDER BY m.created_at ASC
        ');

        $stmt->execute(['group_id' => $group_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
