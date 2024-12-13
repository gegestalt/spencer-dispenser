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
    // Generate 15 users with unique random IDs and usernames
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

    // Add 7 groups with interesting names
    $groups = [
        ['name' => 'Tech Innovators'],
        ['name' => 'Gaming Legends'],
        ['name' => 'Book Enthusiasts'],
        ['name' => 'Fitness Gurus'],
        ['name' => 'Crypto Pioneers'],
        ['name' => 'AI Thinkers'],
        ['name' => 'Travel Explorers']
    ];

    $groupStmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
    foreach ($groups as $group) {
        $groupStmt->execute(['name' => $group['name']]);
    }

    // Assign users to random groups
    $groupIds = $db->query('SELECT id FROM groups')->fetchAll(PDO::FETCH_COLUMN);
    $userIds = $db->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN);

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

    // Add some messages to random groups
    $messages = [
        'This is amazing!',
        'Anyone tried this before?',
        'What are your thoughts on this topic?',
        'Let\'s meet up soon!',
        'Check out this cool feature.',
        'Can you help with this issue?',
        'What\'s the best resource for this?'
    ];

    $messageStmt = $db->prepare('INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :content)');
    foreach ($groupIds as $groupId) {
        foreach (array_rand($userIds, random_int(3, 5)) as $userIndex) {
            $messageStmt->execute([
                'group_id' => $groupId,
                'user_id' => $userIds[$userIndex],
                'content' => $messages[array_rand($messages)]
            ]);
        }
    }

    echo "Database seeded successfully with 15 users, 7 groups, and messages.\n";
}

try {
    $db = getDatabaseConnection();

    // Clear existing database
    clearDatabase($db);

    // Recreate schema
    recreateSchema($db);

    // Seed the database
    seedDatabase($db);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
