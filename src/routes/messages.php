// src/routes/messages.php
<?php

use Slim\App;

return function (App $app) {
    $app->get('/groups/{id}/messages', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $messages = Message::getByGroup($db, $args['id']);
        return $response->withJson($messages);
    });

    $app->post('/groups/{id}/messages', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();
        $id = Message::send($db, $args['id'], $data['user_id'], $data['content']);
        return $response->withJson(['message_id' => $id]);
    });
};
