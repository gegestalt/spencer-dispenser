<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/database.php'; // Database connection

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\BodyParsingMiddleware;

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

(require __DIR__ . '/../src/routes/users.php')($app);

$app->get('/groups', function ($request, $response) {
    $db = getDatabaseConnection();
    $stmt = $db->query('SELECT * FROM groups');
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($groups));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/groups/{id}/messages', function ($request, $response, $args) {
    $db = getDatabaseConnection();
    $groupId = $args['id'];
    $stmt = $db->prepare('
        SELECT messages.*, users.username 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        WHERE group_id = :group_id
    ');
    $stmt->execute(['group_id' => $groupId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

(require __DIR__ . '/../src/routes/messages.php')($app);

$app->run();
