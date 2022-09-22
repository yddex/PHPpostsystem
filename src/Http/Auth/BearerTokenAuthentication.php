<?php

namespace Maxim\Postsystem\Http\Auth;

use DateTimeImmutable;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\AuthTokenNotFound;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\AuthTokenNotFoundException;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\IAuthTokenRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Psr\Log\LoggerInterface;


//Аутентификация по токену из запроса
//header запроса должен содержать ключ- 'Authorization'
//со значением 'Bearer %token%'
class BearerTokenAuthentication implements ITokenAuthentication
{
    private const HEADER_PREFIX = "Bearer ";

    private IAuthTokenRepository $authTokenRepository;
    private IUserRepository $userRepository;

    public function __construct(IAuthTokenRepository $authTokenRepository, IUserRepository $userRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
        $this->userRepository = $userRepository;
    }



    public function user(Request $request): User
    {
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }


        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new HttpException("Malformed token: [$header]");
        }
        $token = mb_substr($header, strlen(self::HEADER_PREFIX));


        try {
            $authToken = $this->authTokenRepository->get($token);
        } catch (AuthTokenNotFoundException) {
            throw new AuthException("Bad token: [$token]");
        }

        if($authToken->getExpiresOn() <= new DateTimeImmutable()){
            throw new AuthException("Token expired: [$token]. LogIn again");
        }

        $userUuid = $authToken->getUserUuid();
        return $this->userRepository->getByUUID($userUuid);
    }
}
