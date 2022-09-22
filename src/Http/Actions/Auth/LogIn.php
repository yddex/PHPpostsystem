<?php

namespace Maxim\Postsystem\Http\Actions\Auth;

use DateTimeImmutable;
use Maxim\Postsystem\Blog\AuthToken;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\IAuthentication;
use Maxim\Postsystem\Http\Auth\Interfaces\IPasswordAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\IAuthTokenRepository;
use Psr\Log\LoggerInterface;


//Класс для получения токена пользователя
//Аутентификация по логину и паролю
//Логин и пароль должны передаваться в JSON с ключами "login" и "password"
class LogIn implements IAction
{
    private IAuthTokenRepository $authTokenRepository;
    private IPasswordAuthentication $passwordAuthentication;
    private LoggerInterface $logger;



    public function __construct(
        IAuthTokenRepository $authTokenRepository,
        IPasswordAuthentication $passwordAuthentication,
        LoggerInterface $logger
    ) {
        $this->authTokenRepository = $authTokenRepository;
        $this->passwordAuthentication = $passwordAuthentication;
        $this->logger = $logger;
    }



    public function handle(Request $request): Response
    {
        //аутентификация пользователя по логину и паролю
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            $this->logger->warning("LOGIN ACTION. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        //создание токена
        $authToken = new AuthToken(
            AuthToken::generate(),
            $user->getUuid(),
            (new DateTimeImmutable())->modify("+1 day")
        );

        $this->authTokenRepository->save($authToken);
        $this->logger->info("LOGIN ACTION. Token register: [" . $authToken->getToken() . "]");

        return new SuccessfulResponse([
            "token" => $authToken->getToken()
        ]);
    }
}
