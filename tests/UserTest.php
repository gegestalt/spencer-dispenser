<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../src/database.php';
require __DIR__ . '/../src/routes/users.php';

class CreateUserTest extends TestCase {
    private $app;
    private $db;

    protected function setUp(): void {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);

        $container = new DI\Container();
        AppFactory::setContainer($container);
        $this->app = AppFactory::create();
        $this->app->addBodyParsingMiddleware();

        (require __DIR__ . '/../src/routes/users.php')($this->app);
    }

    public function testCreateUserSuccess() {
        $request = $this->createRequest('POST', '/users', ['username' => 'TestUser']);
        $response = $this->app->handle($request);

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertArrayHasKey('username', $responseData);
        $this->assertEquals('TestUser', $responseData['username']);

        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => 'TestUser']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($user);
        $this->assertEquals('TestUser', $user['username']);
    }

    public function testCreateUserDuplicate() {
        $this->db->exec("INSERT INTO users (id, username) VALUES (1234, 'ExistingUser')");

        $request = $this->createRequest('POST', '/users', ['username' => 'ExistingUser']);
        $response = $this->app->handle($request);

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Username already exists', $responseData['error']);
    }

    public function testCreateUserValidationError() {
        $request = $this->createRequest('POST', '/users', []);
        $response = $this->app->handle($request);

        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Username is required', $responseData['error']);
    }

    private function createRequest(string $method, string $path, array $data = []): \Psr\Http\Message\ServerRequestInterface {
        $request = (new Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest($method, $path);
        if (!empty($data)) {
            $request = $request->withParsedBody($data);
        }
        return $request;
    }
}