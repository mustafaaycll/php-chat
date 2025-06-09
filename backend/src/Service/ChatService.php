<?php
namespace Mustafaaycll\PhpChat\Service;

use Mustafaaycll\PhpChat\Repository\ChatRepository;
use Mustafaaycll\PhpChat\Repository\UserRepository;
use Mustafaaycll\PhpChat\Repository\MessageRepository;
use Mustafaaycll\PhpChat\Model\Chat;
use Mustafaaycll\PhpChat\Model\Message;
use Mustafaaycll\PhpChat\Model\User;

class ChatService
{
    public function __construct(
        private ChatRepository $chatRepository,
        private UserRepository $userRepository,
        private MessageRepository $messageRepository
    ) {}

    public function createChat(string $username, string $chatName): Chat
    {
        // Ensure user exists
        if (! $this->userRepository->exists($username)) {
            throw new \InvalidArgumentException("User not found: $username");
        }

        return $this->chatRepository->create($chatName, $username);
    }

    public function joinChat(string $username, int $chatId): void
    {
        $user = $this->userRepository->findByUsername($username);
        $chat = $this->chatRepository->findById($chatId);

        if (! $user || ! $chat) {
            throw new \InvalidArgumentException("User or chat not found");
        }

        // Avoid duplicate join
        if (! $chat->users()->where('username', $user->username)->exists()) {
            $chat->users()->attach($user->username);
        }
    }

    public function sendMessage(string $username, int $chatId, string $content, int $sentAt): Message
    {
        // Optional: you can check if user is joined to chat

        if (! $this->userRepository->exists($username)) {
            throw new \InvalidArgumentException("User not found: $username");
        }

        if (! $this->chatRepository->exists($chatId)) {
            throw new \InvalidArgumentException("Chat not found: $chatId");
        }

        return $this->messageRepository->create($username, $chatId, $sentAt, $content);
    }

    public function listMessages(int $chatId): array
    {
        if (! $this->chatRepository->exists($chatId)) {
            throw new \InvalidArgumentException("Chat not found: $chatId");
        }

        return $this->messageRepository->findByChatId($chatId);
    }

    public function listChats(): array
    {
        return $this->chatRepository->findAll();
    }

    public function listChatsJoined(string $username): array
    {
        $user = $this->userRepository->findByUsername($username);

        if (! $user) {
            throw new \InvalidArgumentException("User not found: $username");
        }

        return $user->chatsAttended()->get()->all();
    }

    public function listChatsNotJoined(string $username): array
    {
        $user = $this->userRepository->findByUsername($username);

        if (! $user) {
            throw new \InvalidArgumentException("User not found: $username");
        }

        $joinedChatIds = $user->chatsAttended()->pluck('id')->toArray();

        return Chat::whereNotIn('id', $joinedChatIds)->get()->all();
    }

    public function deleteChat(int $chatId): void
    {
        $chat = $this->chatRepository->findById($chatId);

        if (! $chat) {
            throw new \InvalidArgumentException("Chat not found: $chatId");
        }

        $this->chatRepository->delete($chat);
    }
}