<?php
namespace Mustafaaycll\PhpChat\Repository;

use Mustafaaycll\PhpChat\Model\Chat;

class ChatRepository
{
    public function create(string $name, string $createdBy): Chat
    {
        return Chat::create([
            'name' => $name,
            'created_by' => $createdBy,
        ]);
    }

    public function findById(int $id): ?Chat
    {
        return Chat::find($id);
    }

    public function findAll(): array
    {
        return Chat::all()->all();
    }

    public function delete(Chat $chat): void
    {
        $chat->delete();
    }

    public function exists(int $id): bool
    {
        return Chat::where('id', $id)->exists();
    }
}