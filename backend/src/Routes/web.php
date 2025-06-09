<?php
/**
 * Defines REST API routes.
 * - Chat endpoints: create chat, join chat, send/list messages, list chats.
 * - User endpoints: create user, delete user.
 */

use Slim\App;
use Mustafaaycll\PhpChat\Controller\ChatController;
use Mustafaaycll\PhpChat\Controller\UserController;

return function (App $app): void {
    // Chat endpoints
    $app->post('/chats', [ChatController::class, 'createChat']);
    $app->post('/chats/{chatId}/join', [ChatController::class, 'joinChat']);
    $app->post('/chats/{chatId}/messages', [ChatController::class, 'sendMessage']);
    $app->get('/chats/{chatId}/messages', [ChatController::class, 'listMessages']);
    $app->get('/chats/joined', [ChatController::class, 'listChatsJoined']);
    $app->get('/chats/not-joined', [ChatController::class, 'listChatsNotJoined']);

    // User endpoints
    $app->post('/users', [UserController::class, 'createUser']);
    $app->delete('/users/{username}', [UserController::class, 'deleteUser']);
};