// src/routes/groups.php
<?php

use Slim\App;

return function (App $app) {
    $app->get('/groups', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $groups = Group::getAll($db);
        return $response->withJson($groups);
    });

    $app->post('/groups', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();
        $id = Group::create($db, $data['name']);
        return $response->withJson(['id' => $id, 'name' => $data['name']]);
    });
};
