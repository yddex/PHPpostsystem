<?php
namespace Maxim\Postsystem\Repositories\UserRepositories;

use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\UUID;

interface IUserRepository
{
    public function save(User $user) :void;
    public function getByUUID(UUID $uuid) :User;
    public function getByLogin(string $login) :User;
    
}