<?php

use PHPUnit\Framework\TestCase;

// Include necessary files
require_once __DIR__ . '/../src/database.php'; 
require_once __DIR__ . '/../src/models/Message.php'; // Correct path based on your directory structure

class MessageTest extends TestCase {
    protected function setUp(): void {
        $db = getDatabaseConnection();

        // Create tables if they don't exist
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content TEXT NOT NULL,
            user_id INTEGER NOT NULL,
            group_id INTEGER NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id),
            FOREIGN KEY(group_id) REFERENCES groups(id)
        )");

        // Clear the tables before each test to avoid conflicts
        $db->exec("DELETE FROM messages");
        $db->exec("DELETE FROM users");
        $db->exec("DELETE FROM groups");

        // Insert test users
        $db->exec("INSERT INTO users (id, username) VALUES (1, 'Alice')");
        $db->exec("INSERT INTO users (id, username) VALUES (2, 'Bob')");

        // Insert test groups
        $db->exec("INSERT INTO groups (id, name) VALUES (1, 'Test Group')");
    }

    public function testSendMessage() {
    $db = getDatabaseConnection();

    $messageId = Message::send($db, 1, 2, 'Hello World'); // Group ID = 1, User ID = 2 (Bob)

    $this->assertIsInt((int) $messageId);

    // Verify the message is saved in the database
    $stmt = $db->prepare("SELECT * FROM messages WHERE id = :id");
    $stmt->execute(['id' => $messageId]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    $this->assertNotEmpty($message);
    $this->assertEquals(1, $message['group_id']);
    $this->assertEquals(2, $message['user_id']);
    $this->assertEquals('Hello World', $message['content']);
}

}
