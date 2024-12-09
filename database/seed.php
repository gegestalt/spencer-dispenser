<?php

require __DIR__ . '/../src/database.php';

function seedDatabase(PDO $db) {
    // Seed users
    $users = [
        ['username' => 'Alice'],
        ['username' => 'Bob'],
        ['username' => 'Charlie']
    ];
    $userStmt = $db->prepare('INSERT INTO users (username) VALUES (:username)');
    foreach ($users as $user) {
        $userStmt->execute(['username' => $user['username']]);
    }

    // Seed groups
    $groups = [
        ['name' => 'General'],
        ['name' => 'Sports'],
        ['name' => 'Technology']
    ];
    $groupStmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
    foreach ($groups as $group) {
        $groupStmt->execute(['name' => $group['name']]);
    }

    // Seed messages
    $messages = [
        ['group_id' => 1, 'user_id' => 1, 'content' => 'Hello everyone!'],
        ['group_id' => 1, 'user_id' => 2, 'content' => 'Hi Alice!'],
        ['group_id' => 2, 'user_id' => 3, 'content' => 'Who watched the game last night?'],
        ['group_id' => 3, 'user_id' => 1, 'content' => 'Has anyone tried the new AI tools?']
    ];
    $messageStmt = $db->prepare('INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :content)');
    foreach ($messages as $message) {
        $messageStmt->execute([
            'group_id' => $message['group_id'],
            'user_id' => $message['user_id'],
            'content' => $message['content']
        ]);
    }

    echo "Database seeded successfully.\n";
}

try {
    $db = getDatabaseConnection();
    seedDatabase($db);
} catch (PDOException $e) {
    echo "Error seeding database: " . $e->getMessage() . "\n";
}
