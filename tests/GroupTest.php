// tests/GroupTest.php
<?php

use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase {
    public function testCreateGroup() {
        $db = getDatabaseConnection();
        $groupId = Group::create($db, 'Test Group');
        $this->assertIsInt($groupId);
    }
}
