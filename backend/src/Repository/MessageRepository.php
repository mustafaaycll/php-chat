<?php
namespace Mustafaaycll\PhpChat\Repository;

use Mustafaaycll\PhpChat\Model\Message;

class MessageRepository
{
    public function create(string $sentBy, int $sentTo, int $sentAt, string $content): Message
    {
        return Message::create([
            'sent_by' => $sentBy,
            'sent_to' => $sentTo,
            'sent_at' => $sentAt,
            'content' => $content,
        ]);
    }

    public function findByChatId(int $chatId): array
    {
        return Message::where('sent_to', $chatId)
                     ->orderBy('sent_at', 'asc')
                     ->get()
                     ->all();
    }
}