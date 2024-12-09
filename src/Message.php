<?php

class Message
{
    /**
     * Send a new message to the database.
     *
     * @param PDO $db The database connection.
     * @param int $groupId The ID of the group to send the message to.
     * @param int $userId The ID of the user sending the message.
     * @param string $content The message content.
     * @return int The ID of the newly created message.
     * @throws Exception If the database operation fails.
     */
    public static function send(PDO $db, int $groupId, int $userId, string $content): int
    {
        // Validate input
        if (empty($content)) {
            throw new InvalidArgumentException('Message content cannot be empty.');
        }

        // Insert the message into the database
        $stmt = $db->prepare('
            INSERT INTO messages (group_id, user_id, content, created_at) 
            VALUES (:group_id, :user_id, :content, :created_at)
        ');
        $createdAt = date('Y-m-d H:i:s');

        if (!$stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
            'content' => $content,
            'created_at' => $createdAt,
        ])) {
            throw new Exception('Failed to insert the message into the database.');
        }

        return (int)$db->lastInsertId();
    }
}
