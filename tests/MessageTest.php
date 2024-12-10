<?php

use PHPUnit\Framework\TestCase;
use App\Models\MessageModel;

class MessageTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);

        $this->db->exec("INSERT INTO users (id, username) VALUES (1, 'testuser')");
        $this->db->exec("INSERT INTO groups (id, name) VALUES (1, 'Test Group')");
        $this->db->exec("INSERT INTO group_memberships (user_id, group_id) VALUES (1, 1)");
    }

    public function testSendMessage() {
        $messageId = MessageModel::send($this->db, 1, 1, 'Hello, world!');
        $this->assertIsInt($messageId);

        $stmt = $this->db->prepare('SELECT content FROM messages WHERE id = :id');
        $stmt->execute(['id' => $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Hello, world!', $message['content']);
    }

    public function testGetMessagesByGroup() {
        $this->db->exec("INSERT INTO messages (group_id, user_id, content, created_at) VALUES (1, 1, 'Test message', datetime('now'))");

        $messages = MessageModel::getByGroup($this->db, 1);
        $this->assertNotEmpty($messages);
        $this->assertEquals('Test message', $messages[0]['content']);
    }

    public function testSendMessageToGroupUserDoesNotBelongTo() {
        $this->db->exec("INSERT INTO users (id, username) VALUES (2, 'otheruser')");
        $this->db->exec("INSERT INTO groups (id, name) VALUES (2, 'Another Group')");

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User is not a member of the group');

        $stmt = $this->db->prepare('
            SELECT COUNT(*) 
            FROM group_memberships 
            WHERE group_id = :group_id AND user_id = :user_id
        ');
        $stmt->execute([
            'group_id' => 2,
            'user_id' => 2,
        ]);

        if ($stmt->fetchColumn() == 0) {
            throw new Exception('User is not a member of the group');
        }

        MessageModel::send($this->db, 2, 2, 'Invalid message');
    }
}
