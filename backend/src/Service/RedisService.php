<?php
namespace Mustafaaycll\PhpChat\Service;

use Predis\Client;

class RedisService
{
    private Client $redis;
    private const CHANNEL = 'php-chat';

    public function __construct()
    {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => 'broker',
            'port'   => 6379,
        ]);
    }

    public function publish(array $message): void
    {
        $this->redis->publish(self::CHANNEL, json_encode($message));
    }
}