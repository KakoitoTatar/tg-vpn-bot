<?php
declare(strict_types=1);

namespace App\Domain\User;

interface UserRepository
{
    /**
     * @param int $id
     * @return User|null\
     */
    public function getInactiveUser(int $id): ?User;

    /**
     * @param User $user
     * @return mixed
     */
    public function save(User $user): User;

    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserOfId(int $id): User;
}
