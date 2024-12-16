<?php
use Slim\App;
use App\Models\MessageModel;

return function (App $app) { 
    $app->post('/groups/{id}/messages', function ($request, $response, $args) {
        $db = getDatabaseConnection();
        $data = $request->getParsedBody();

        if (!isset($data['user_id']) || !isset($data['content'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing user_id or content']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $db->prepare('
            SELECT COUNT(*) 
            FROM group_memberships 
            WHERE group_id = :group_id AND user_id = :user_id
        ');
        $stmt->execute([
            'group_id' => $args['id'],
            'user_id' => $data['user_id'],
        ]);

        if ($stmt->fetchColumn() == 0) {
            $response->getBody()->write(json_encode(['error' => 'User is not a member of the group']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        try {
            $messageId = MessageModel::send($db, $args['id'], $data['user_id'], $data['content']); // Fixed here
            $response->getBody()->write(json_encode(['message_id' => $messageId]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
