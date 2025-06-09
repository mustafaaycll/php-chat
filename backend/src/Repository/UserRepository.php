<?php
namespace Mustafaaycll\PhpChat\Repository;

use Mustafaaycll\PhpChat\Model\User;

class UserRepository
{
    public function create(string $username): User
    {
        return User::create([
            'username' => $username,
        ]);
    }

    public function findByUsername(string $username): ?User
    {
        return User::find($username);
    }

    public function exists(string $username): bool
    {
        return User::where('username', $username)->exists();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}