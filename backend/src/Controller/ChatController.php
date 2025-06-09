<?php
namespace Mustafaaycll\PhpChat\Controller;

use Psr\Log\LoggerInterface;
use Mustafaaycll\PhpChat\Service\ChatService;
use Mustafaaycll\PhpChat\Service\RedisService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChatController
{
    public function __construct(
        private ChatService $chatService,
        private RedisService $redisService,
        private LoggerInterface $logger
    ) {}

    public function createChat(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (array) $request->getParsedBody();

        $username = $data['username'] ?? null;
        $chatName = $data['name'] ?? null;

        $this->logger->info("Creating chat", [
            'username' => $username,
            'chatName' => $chatName
        ]);

        if (!$username || !$chatName) {
            $response->getBody()->write(json_encode(['error' => 'Missing username or chat name']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $chat = $this->chatService->createChat($username, $chatName);

        // After creation, also join creator to the chat
        $this->chatService->joinChat($username, $chat->id);

        $this->redisService->publish([
            'type' => 'chat_created',
            'chatId' => $chat->id,
            'chatName' => $chat->name,
            'createdBy' => $username,
        ]);

        return $response->withStatus(201); // Created
    }

    public function joinChat(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        $username = $data['username'] ?? null;
        $chatId = (int) $args['chatId'];

        $this->logger->info("Joining chat", [
            'username' => $username,
            'chatId'   => $chatId
        ]);

        if (!$username) {
            $response->getBody()->write(json_encode(['error' => 'Missing username']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $this->chatService->joinChat($username, $chatId);

        $this->redisService->publish([
            'type' => 'chat_created',
            'chatId' => $chatId,
            'chatName' => '',
            'createdBy' => $username,
        ]);

        return $response->withStatus(204); // No Content
    }

    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = (array) $request->getParsedBody();

        $username = $data['username'] ?? null;
        $content = $data['content'] ?? null;
        $sentAt = $data['sent_at'] ?? time();
        $chatId = (int) $args['chatId'];

        $this->logger->info("Sending message", [
            'username' => $username,
            'chatId'   => $chatId,
            'content'  => $content,
            'sentAt'   => $sentAt
        ]);

        if (!$username || !$content) {
            $response->getBody()->write(json_encode(['error' => 'Missing username or content']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $this->chatService->sendMessage($username, $chatId, $content, (int) $sentAt);

        $this->redisService->publish([
            'type' => 'message_sent',
            'chatId' => $chatId,
            'username' => $username,
            'content' => $content,
            'sentAt' => $sentAt,
        ]);
        return $response->withStatus(201); // Created
    }

    public function listMessages(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $chatId = (int) $args['chatId'];

        $this->logger->info("Listing messages for", [
            'chatId' => $chatId
        ]);

        $messages = $this->chatService->listMessages($chatId);
        
        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listChatsJoined(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $username = $queryParams['username'] ?? null;

        $this->logger->info("Listing chats joined by", [
            'username' => $username
        ]);

        if (!$username) {
            $response->getBody()->write(json_encode(['error' => 'Missing username']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $chats = $this->chatService->listChatsJoined($username);

        $response->getBody()->write(json_encode($chats));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listChatsNotJoined(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $username = $queryParams['username'] ?? null;

        $this->logger->info("Listing chats not joined by", [
            'username' => $username
        ]);

        if (!$username) {
            $response->getBody()->write(json_encode(['error' => 'Missing username']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $chats = $this->chatService->listChatsNotJoined($username);

        $response->getBody()->write(json_encode($chats));
        return $response->withHeader('Content-Type', 'application/json');
    }
}