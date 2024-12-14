<?php

require __DIR__ . '/../src/database.php';

function clearDatabase(PDO $db) {
    $tables = ['messages', 'group_memberships', 'groups', 'users'];
    foreach ($tables as $table) {
        $db->exec("DROP TABLE IF EXISTS $table");
    }
    echo "Database cleared successfully.\n";
}

function recreateSchema(PDO $db) {
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    if (!$schema) {
        throw new Exception("Failed to load schema file.");
    }
    $db->exec($schema);
    echo "Schema recreated successfully.\n";
}

function seedDatabase(PDO $db) {
    // Seed Users
    $users = [];
    for ($i = 1; $i <= 15; $i++) {
        $users[] = [
            'id' => random_int(1000, 9999), // Random unique ID
            'username' => 'User' . $i
        ];
    }

    $userStmt = $db->prepare('INSERT INTO users (id, username) VALUES (:id, :username)');
    foreach ($users as $user) {
        $userStmt->execute(['id' => $user['id'], 'username' => $user['username']]);
    }

    // Seed Groups
    $groups = [
        ['name' => 'Tech Innovators'],
        ['name' => 'Gaming Legends'],
        ['name' => 'Book Enthusiasts'],
        ['name' => 'Fitness Gurus'],
        ['name' => 'Crypto Pioneers'],
        ['name' => 'AI Thinkers'],
        ['name' => 'Travel Explorers']
    ];

    $userIds = $db->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN); // Get all user IDs
    $groupStmt = $db->prepare('INSERT INTO groups (name, created_by, created_at) VALUES (:name, :created_by, :created_at)');
    foreach ($groups as $group) {
        $groupStmt->execute([
            'name' => $group['name'],
            'created_by' => $userIds[array_rand($userIds)], // Assign a random user as the creator
            'created_at' => date('Y-m-d H:i:s') // Current timestamp
        ]);
    }

    // Assign Users to Random Groups
    $groupIds = $db->query('SELECT id FROM groups')->fetchAll(PDO::FETCH_COLUMN);
    $membershipStmt = $db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
    foreach ($userIds as $userId) {
        $joinedGroups = array_rand($groupIds, random_int(1, 3)); // 1-3 random groups
        foreach ((array) $joinedGroups as $groupIndex) {
            $membershipStmt->execute([
                'user_id' => $userId,
                'group_id' => $groupIds[$groupIndex]
            ]);
        }
    }

    // Add Messages to Random Groups
    $messages = [
        'This is amazing!',
        'Anyone tried this before?',
        'What are your thoughts on this topic?',
        'Let\'s meet up soon!',
        'Check out this cool feature.',
        'Can you help with this issue?',
        'What\'s the best resource for this?'
    ];

    $messageStmt = $db->prepare('INSERT INTO messages (group_id, user_id, content, created_at) VALUES (:group_id, :user_id, :content, :created_at)');
    foreach ($groupIds as $groupId) {
        foreach (array_rand($userIds, random_int(3, 5)) as $userIndex) {
            $messageStmt->execute([
                'group_id' => $groupId,
                'user_id' => $userIds[$userIndex],
                'content' => $messages[array_rand($messages)],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    echo "Database seeded successfully with 15 users, 7 groups, and messages.\n";
}

try {
    $db = getDatabaseConnection();

    clearDatabase($db);

    recreateSchema($db);

    seedDatabase($db);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
