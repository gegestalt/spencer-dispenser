<?php
// src/routes/groups.php
use Slim\App;

return function (App $app) {
    $app->post('/groups', function ($request, $response) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();

        if (empty($data['name'])) {
            $response->getBody()->write(json_encode(['error' => 'Group name is required.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
        $stmt->execute(['name' => $data['name']]);

        $response->getBody()->write(json_encode(['group_id' => $db->lastInsertId()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });

    $app->post('/groups/{group_id}/join', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();
        $groupId = $args['group_id'];

        if (empty($data['username'])) {
            $response->getBody()->write(json_encode(['error' => 'Username is required.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $username = $data['username'];

        try {
            // Check if the group exists
            $stmt = $db->prepare('SELECT name FROM groups WHERE id = :group_id');
            $stmt->execute(['group_id' => $groupId]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$group) {
                $response->getBody()->write(json_encode(['error' => 'Group does not exist.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // Check if the user exists
            $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $response->getBody()->write(json_encode(['error' => 'User does not exist.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $userId = $user['id'];

            // Check if the user is already in the group
            $stmt = $db->prepare('SELECT COUNT(*) FROM group_memberships WHERE user_id = :user_id AND group_id = :group_id');
            $stmt->execute(['user_id' => $userId, 'group_id' => $groupId]);
            if ($stmt->fetchColumn() > 0) {
                $response->getBody()->write(json_encode(['error' => 'User is already a member of the group.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Add the user to the group
            $stmt = $db->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
            $stmt->execute(['user_id' => $userId, 'group_id' => $groupId]);

            // Return success response
            $response->getBody()->write(json_encode([
                'success' => true,
                'user_id' => $userId,
                'username' => $username,
                'group_id' => $groupId,
                'group_name' => $group['name']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
