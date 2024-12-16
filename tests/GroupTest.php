<?php

use PHPUnit\Framework\TestCase;

// Include the database connection
require __DIR__ . '/../src/database.php';

class GroupTest extends TestCase {
    private $db;

    protected function setUp(): void {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $this->db->exec('PRAGMA foreign_keys = ON;');
    
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);
    
        $this->db->exec("INSERT INTO users (id, username) VALUES (1, 'Alice')");
        $this->db->exec("INSERT INTO groups (id, name, created_by, created_at) VALUES (1, 'General', 1, CURRENT_TIMESTAMP)");
        $this->db->exec("INSERT INTO group_memberships (user_id, group_id) VALUES (1, 1)");
    }

    public function testCreateGroup() {
        $stmt = $this->db->prepare('INSERT INTO groups (name, created_by, created_at) VALUES (:name, :created_by, CURRENT_TIMESTAMP)');
        $stmt->execute(['name' => 'New Group', 'created_by' => 1]);

        $groupId = $this->db->lastInsertId();

        $stmt = $this->db->prepare('SELECT * FROM groups WHERE id = :id');
        $stmt->execute(['id' => $groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($group);
        $this->assertEquals('New Group', $group['name']);
        $this->assertEquals(1, $group['created_by']);
        $this->assertNotEmpty($group['created_at']);

    }

    public function testCreateGroupWithDuplicateName() {
        $this->expectException(PDOException::class);

        $stmt = $this->db->prepare('INSERT INTO groups (name, created_by, created_at) VALUES (:name, :created_by, CURRENT_TIMESTAMP)');
        $stmt->execute(['name' => 'General', 'created_by' => 1]);
    }

    public function testJoinGroup() {
        $this->db->exec("INSERT INTO users (id, username) VALUES (2, 'Bob')");

        $stmt = $this->db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
        $stmt->execute(['user_id' => 2, 'group_id' => 1]);

        $stmt = $this->db->prepare('SELECT * FROM group_memberships WHERE user_id = :user_id AND group_id = :group_id');
        $stmt->execute(['user_id' => 2, 'group_id' => 1]);
        $membership = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($membership);
        $this->assertEquals(2, $membership['user_id']);
        $this->assertEquals(1, $membership['group_id']);
    }

    public function testJoinGroupAgain() {
        $this->db->exec("INSERT INTO users (id, username) VALUES (2, 'Bob')");

        $stmt = $this->db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
        $stmt->execute(['user_id' => 2, 'group_id' => 1]);

        // Attempt to add the user to the same group again
        $this->expectException(PDOException::class);
        $stmt->execute(['user_id' => 2, 'group_id' => 1]);
    }

    public function testUserCannotJoinNonexistentGroup() {
        $this->db->exec("INSERT INTO users (id, username) VALUES (2, 'Bob')");

        $stmt = $this->db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');

        $this->expectException(PDOException::class);
        $stmt->execute(['user_id' => 2, 'group_id' => 999]); // Nonexistent group_id
    }

    public function testListGroups() {
        $this->db->exec("INSERT INTO groups (id, name, created_by, created_at) VALUES (2, 'Tech Group', 1, CURRENT_TIMESTAMP)");

        $stmt = $this->db->query('SELECT * FROM groups');
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertGreaterThanOrEqual(2, count($groups)); // Includes "General" and "Tech Group"
    }

    protected function tearDown(): void {
        $this->db->exec("DELETE FROM group_memberships");
        $this->db->exec("DELETE FROM groups");
        $this->db->exec("DELETE FROM users");
        $this->db = null;
    }
}
