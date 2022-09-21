<?php

namespace Maxim\Postsystem\Http\Auth;

use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;

class JsonBodyLoginIdentification implements IAuthentication
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function user(Request $request): User
    {
        try {
            // Получаем имя пользователя из JSON-тела запроса;
            // ожидаем, что имя пользователя находится в поле login
            $username = $request->jsonBodyField('login');
        } catch (HttpException $e) {
           
            throw new AuthException($e->getMessage());
        }
        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
           
            throw new AuthException($e->getMessage());
        }
    }
}
