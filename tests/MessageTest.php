<?php
use PHPUnit\Framework\TestCase;
use App\Models\MessageModel;

class MessageTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new RuntimeException('Schema file not found. Please ensure the schema.sql file exists.');
        }

        $schema = file_get_contents($schemaFile);
        $this->db->exec($schema);

        // Verify that required tables exist
        $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('messages', $tables)) {
            throw new RuntimeException('Database does not have required tables. Ensure the schema file is correct.');
        }

        $this->db->exec("INSERT INTO users (username) VALUES ('test_creator')");
    }

    public function testSendMessage() {
        $userId = $this->db->lastInsertId(); // Get the ID of the inserted user
        $this->db->exec("INSERT INTO groups (name, created_by) VALUES ('Test Group', $userId)");
        $groupId = $this->db->lastInsertId();

        $messageId = MessageModel::send($this->db, $groupId, $userId, 'Hello, world!');
        $this->assertIsNumeric($messageId);

        $stmt = $this->db->prepare('SELECT content FROM messages WHERE id = :id');
        $stmt->execute(['id' => $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Hello, world!', $message['content']);
    }

    public function testGetMessagesByGroup() {
        $userId = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO groups (name, created_by) VALUES ('Test Group', $userId)");
        $groupId = $this->db->lastInsertId();

        $this->db->exec("INSERT INTO messages (group_id, user_id, content) VALUES ($groupId, $userId, 'Test message')");
        
        // Fetch messages by group
        $messages = MessageModel::getByGroup($this->db, $groupId);

        $this->assertNotEmpty($messages);
        $this->assertEquals('Test message', $messages[0]['content']);
    }

    protected function tearDown(): void {
        $this->db = null;
    }
}
