<?php
namespace Mustafaaycll\PhpChat\Controller;

use Psr\Log\LoggerInterface;
use Mustafaaycll\PhpChat\Service\UserService;
use Mustafaaycll\PhpChat\Service\RedisService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    public function __construct(
        private UserService $userService,
        private RedisService $redisService,
        private LoggerInterface $logger
    ) {}

    public function createUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        $username = $data['username'] ?? null;

        $this->logger->info("Creating user", [
            'username' => $username
        ]);

        if (!$username) {
            $response->getBody()->write(json_encode(['error' => 'Missing username']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $this->userService->createUser($username);

        return $response->withStatus(201); // Created
    }

    public function deleteUser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $username = $args['username'] ?? null;

        $this->logger->info("Deleting user", [
            'username' => $username
        ]);

        if (!$username) {
            $response->getBody()->write(json_encode(['error' => 'Missing username']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $this->userService->deleteUser($username);

        return $response->withStatus(204); // No Content
    }
}