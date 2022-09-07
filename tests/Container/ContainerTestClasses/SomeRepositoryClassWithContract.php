<?php
namespace Maxim\Postsystem\UnitTests\Container\ContainerTestClasses;

use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\Container\NotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;

class SomeRepositoryClassWithContract implements IUserRepository
{
    public function save(User $user): void
    {
        
    }

    public function getByLogin(string $login): User
    {
        throw new UserNotFoundException("not found");
    }

    public function getByUUID(UUID $uuid): User
    {
        throw new UserNotFoundException("not found");
    }
}