<?php

require __DIR__ . '/../src/database.php';

function seedDatabase(PDO $db) {
    // Seed users
    $users = [
        ['id' => 1, 'username' => 'Alice'],
        ['id' => 2, 'username' => 'Bob'],
        ['id' => 3, 'username' => 'Charlie']
    ];
    $userStmt = $db->prepare('INSERT INTO users (id, username) VALUES (:id, :username)');
    foreach ($users as $user) {
        $userStmt->execute(['id' => $user['id'], 'username' => $user['username']]);
    }

    // Seed groups
    $groups = [
        ['id' => 1, 'name' => 'General'],
        ['id' => 2, 'name' => 'Sports'],
        ['id' => 3, 'name' => 'Technology']
    ];
    $groupStmt = $db->prepare('INSERT INTO groups (id, name) VALUES (:id, :name)');
    foreach ($groups as $group) {
        $groupStmt->execute(['id' => $group['id'], 'name' => $group['name']]);
    }

    // Seed memberships
    $memberships = [
        ['user_id' => 1, 'group_id' => 1],
        ['user_id' => 2, 'group_id' => 1],
        ['user_id' => 3, 'group_id' => 2]
    ];
    $membershipStmt = $db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
    foreach ($memberships as $membership) {
        $membershipStmt->execute(['user_id' => $membership['user_id'], 'group_id' => $membership['group_id']]);
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