<?php
namespace Mustafaaycll\PhpChat\Service;

use Mustafaaycll\PhpChat\Repository\UserRepository;
use Mustafaaycll\PhpChat\Model\User;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function createUser(string $username): User
    {
        $existingUser = $this->userRepository->findByUsername($username);

        if ($existingUser) {
            // User already exists → just return it (no exception)
            return $existingUser;
        }

        // User does not exist → create new one
        return $this->userRepository->create($username);
    }

    public function deleteUser(string $username): void
    {
        $user = $this->userRepository->findByUsername($username);

        if (! $user) {
            throw new \InvalidArgumentException("User not found: $username");
        }

        $this->userRepository->delete($user);
    }
}