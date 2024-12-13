<?php

use PHPUnit\Framework\TestCase;
use App\Models\MessageModel;

// Include database connection
require __DIR__ . '/../src/database.php';

class MessageTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = getDatabaseConnection();

        $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('messages', $tables)) {
            throw new RuntimeException('Database does not have required tables. Run the seed script.');
        }
    }

    public function testSendMessage() {
        $this->db->exec("INSERT INTO users (username) VALUES ('testuser')");
        $userId = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO groups (name) VALUES ('Test Group')");
        $groupId = $this->db->lastInsertId();

        $messageId = MessageModel::send($this->db, $groupId, $userId, 'Hello, world!');
        $this->assertIsNumeric($messageId);

        $stmt = $this->db->prepare('SELECT content FROM messages WHERE id = :id');
        $stmt->execute(['id' => $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Hello, world!', $message['content']);
    }
    public function testGetMessagesByGroup() {
        $this->db->exec("INSERT INTO messages (group_id, user_id, content) VALUES (1, 1, 'Test message')");
        $messages = MessageModel::getByGroup($this->db, 1);

        $this->assertNotEmpty($messages);
        $this->assertEquals('Test message', $messages[0]['content']);
    }
    protected function tearDown(): void {
        $this->db->exec("DELETE FROM messages");
        $this->db->exec("DELETE FROM group_memberships");
        $this->db->exec("DELETE FROM groups");
        $this->db->exec("DELETE FROM users");
    
        $this->db = null;
    }
    
}
