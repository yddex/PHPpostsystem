<?php

namespace Maxim\Postsystem\Http\Auth;

use InvalidArgumentException;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;

class JsonBodyUuidIdentification implements IdentificationInterface
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function user(Request $request): User
    {
        try {
    
            $userUuid = new UUID($request->jsonBodyField('author_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {

            throw new AuthException($e->getMessage());
        }
        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->userRepository->getByUUID($userUuid);
        } catch (UserNotFoundException $e) {

            throw new AuthException($e->getMessage());
        }
    }
}
