<?php
// src/routes/groups.php
use Slim\App;

return function (App $app) {
    $app->post('/groups', function ($request, $response) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();

        if (empty($data['name'])) {
            return $response->withJson(['error' => 'Group name is required.'], 400);
        }

        $stmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
        $stmt->execute(['name' => $data['name']]);

        return $response->withJson(['group_id' => $db->lastInsertId()], 201);
    });

    $app->post('/groups/{id}/join', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();

        if (empty($data['user_id'])) {
            return $response->withJson(['error' => 'User ID is required.'], 400);
        }

        $stmt = $db->prepare('
            INSERT OR IGNORE INTO group_memberships (user_id, group_id) 
            VALUES (:user_id, :group_id)
        ');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'group_id' => $args['id'],
        ]);

        return $response->withJson(['message' => 'User joined the group successfully.']);
    });
};
