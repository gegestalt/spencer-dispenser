<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/database.php'; // Include the database connection

use Slim\Factory\AppFactory;
use DI\Container;

// Create the container
$container = new Container();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->get('/groups', function ($request, $response, $args) {
    $db = getDatabaseConnection();  // Get database connection
    $stmt = $db->query('SELECT * FROM groups');
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($groups));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/groups/{id}/messages', function ($request, $response, $args) {
    $groupId = $args['id'];
    $db = getDatabaseConnection();  
    $stmt = $db->prepare('
        SELECT messages.*, users.username 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        WHERE group_id = :group_id
    ');
    $stmt->execute(['group_id' => $groupId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Manually encode to JSON and set the content type header
    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run the application
$app->run();
