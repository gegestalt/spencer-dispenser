<?php

use PHPUnit\Framework\TestCase;

// Include the database connection
require __DIR__ . '/../src/database.php';

class GroupTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = new PDO('sqlite::memory:'); // Use in-memory database for testing
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Load the schema
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);

        // Insert minimal test data
        $this->db->exec("INSERT INTO users (id, username) VALUES (1, 'Alice')");
        $this->db->exec("INSERT INTO groups (id, name) VALUES (1, 'General')");
        $this->db->exec("INSERT INTO group_memberships (user_id, group_id) VALUES (1, 1)");
    }

    public function testCreateGroup() {
        $groupId = Group::create($this->db, 'Test Group');
        $this->assertIsNumeric($groupId);

        $stmt = $this->db->prepare('SELECT * FROM groups WHERE id = :id');
        $stmt->execute(['id' => $groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($group);
        $this->assertEquals('Test Group', $group['name']);
    }

    public function testListGroups() {
        Group::create($this->db, 'Group A');
        Group::create($this->db, 'Group B');

        $stmt = $this->db->query('SELECT * FROM groups');
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertGreaterThanOrEqual(2, count($groups));
    }

    public function testJoinGroup() {
        // Add a user and a group
        $this->db->exec("INSERT INTO users (username) VALUES ('testuser')");
        $userId = $this->db->lastInsertId();

        $groupId = Group::create($this->db, 'Joinable Group');

        // Join the group
        $joined = Group::join($this->db, $userId, $groupId);
        $this->assertTrue($joined);

        $stmt = $this->db->prepare('SELECT * FROM group_memberships WHERE user_id = :user_id AND group_id = :group_id');
        $stmt->execute(['user_id' => $userId, 'group_id' => $groupId]);
        $membership = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($membership);
    }

    public function testAnyUserCanJoinAnyGroup() {
        $this->db->exec("INSERT INTO users (username) VALUES ('User1')");
        $userId1 = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO users (username) VALUES ('User2')");
        $userId2 = $this->db->lastInsertId();

        $groupId1 = Group::create($this->db, 'Group1');
        $groupId2 = Group::create($this->db, 'Group2');

        $this->assertTrue(Group::join($this->db, $userId1, $groupId1));

        $this->assertTrue(Group::join($this->db, $userId1, $groupId2));

        $this->assertTrue(Group::join($this->db, $userId2, $groupId1));

        $stmt = $this->db->prepare('SELECT * FROM group_memberships WHERE user_id = :user_id AND group_id = :group_id');

        $stmt->execute(['user_id' => $userId1, 'group_id' => $groupId1]);
        $this->assertNotEmpty($stmt->fetch(PDO::FETCH_ASSOC));

        $stmt->execute(['user_id' => $userId1, 'group_id' => $groupId2]);
        $this->assertNotEmpty($stmt->fetch(PDO::FETCH_ASSOC));

        $stmt->execute(['user_id' => $userId2, 'group_id' => $groupId1]);
        $this->assertNotEmpty($stmt->fetch(PDO::FETCH_ASSOC));
    }

    protected function tearDown(): void {
        $this->db->exec("DELETE FROM messages");
        $this->db->exec("DELETE FROM group_memberships");
        $this->db->exec("DELETE FROM groups");
        $this->db->exec("DELETE FROM users");

        $this->db = null;
    }
}

class Group {
    public static function create(PDO $db, string $name): int {
        $stmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
        return (int) $db->lastInsertId();
    }

    public static function join(PDO $db, int $userId, int $groupId): bool {
        $stmt = $db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
        return $stmt->execute(['user_id' => $userId, 'group_id' => $groupId]);
    }
}
