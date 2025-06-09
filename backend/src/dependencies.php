<?php
/**
 * Registers services, repositories, and controllers in the DI container.
 * - Sets up UserService, ChatService, RedisService.
 * - Sets up UserRepository, ChatRepository, MessageRepository.
 * - Sets up Monolog logger.
 */

use DI\Container;
use Mustafaaycll\PhpChat\Controller\UserController;
use Mustafaaycll\PhpChat\Controller\ChatController;
use Mustafaaycll\PhpChat\Service\UserService;
use Mustafaaycll\PhpChat\Service\ChatService;
use Mustafaaycll\PhpChat\Service\RedisService;
use Mustafaaycll\PhpChat\Repository\UserRepository;
use Mustafaaycll\PhpChat\Repository\ChatRepository;
use Mustafaaycll\PhpChat\Repository\MessageRepository;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return function (Container $container): void {

    // Repositories
    $container->set(UserRepository::class, new UserRepository());
    $container->set(ChatRepository::class, new ChatRepository());
    $container->set(MessageRepository::class, new MessageRepository());

    // Services
    $container->set(UserService::class, function(Container $c) {
        return new UserService($c->get(UserRepository::class));
    });

    $container->set(ChatService::class, function(Container $c) {
        return new ChatService(
            $c->get(ChatRepository::class),
            $c->get(UserRepository::class),
            $c->get(MessageRepository::class)
        );
    });

    $container->set(RedisService::class, function() {
        return new RedisService();
    });

    // Controllers
    $container->set(UserController::class, function(Container $c) {
        return new UserController(
            $c->get(UserService::class),
            $c->get(RedisService::class),
            $c->get(Logger::class)
        );
    });

    $container->set(ChatController::class, function(Container $c) {
        return new ChatController(
            $c->get(ChatService::class),
            $c->get(RedisService::class),
            $c->get(Logger::class)
        );
    });

    // Logger
    $container->set(Logger::class, function() {
        $logger = new Logger('php-chat');
        $logger->pushHandler(new StreamHandler('php://stdout'));
        return $logger;
    });
};