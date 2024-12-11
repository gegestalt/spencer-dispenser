<?php

use PHPUnit\Framework\TestCase;

// Include the database connection
require __DIR__ . '/../src/database.php';

class GroupTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = getDatabaseConnection();

        // Verify the database has tables
        $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('groups', $tables)) {
            throw new RuntimeException('Database does not have required tables. Run the seed script.');
        }
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

    protected function tearDown(): void {
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
