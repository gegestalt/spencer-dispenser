<?php

use Slim\App;

return function (App $app) {
    $app->post('/users', function ($request, $response) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();

        if (!isset($data['username']) || empty($data['username'])) {
            $response->getBody()->write(json_encode(['error' => 'Username is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $username = $data['username'];

        try {
            $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]); //check username 
            if ($stmt->fetchColumn() > 0) {
                $response->getBody()->write(json_encode(['error' => 'Username already exists']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $uniqueId = random_int(1000, 9999);

            $stmt = $db->prepare('INSERT INTO users (id, username) VALUES (:id, :username)');
            $stmt->execute([
                'id' => $uniqueId,
                'username' => $username
            ]);

            $response->getBody()->write(json_encode([
                'success' => true,
                'user_id' => $uniqueId,
                'username' => $username
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
