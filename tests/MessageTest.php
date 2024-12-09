// tests/MessageTest.php
<?php

use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {
    public function testSendMessage() {
        $db = getDatabaseConnection();
        $messageId = Message::send($db, 1, 1, 'Hello World');
        $this->assertIsInt($messageId);
    }
}
