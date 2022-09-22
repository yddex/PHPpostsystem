<?php

namespace Maxim\Postsystem\Http\Actions\Auth;

use DateTimeImmutable;
use Maxim\Postsystem\Blog\AuthToken;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\IAuthTokenRepository;
use Psr\Log\LoggerInterface;

class Logout implements IAction
{
    private IAuthTokenRepository $tokenRepository;
    private ITokenAuthentication $userAuthentication;
    private LoggerInterface $logger;

    public function __construct(IAuthTokenRepository $tokenRepository, ITokenAuthentication $userAuthentication, LoggerInterface $logger)
    {
        $this->tokenRepository = $tokenRepository;
        $this->userAuthentication = $userAuthentication;
        $this->logger = $logger;
    }


    public function handle(Request $request): Response
    {
        try {
            $token = $request->jsonBodyField("token");
            $user = $this->userAuthentication->user($request);
        } catch (HttpException | AuthException $e) {
            $this->logger->warning("LOGOUT. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        $authToken = $this->tokenRepository->get($token);
        $expiredAuthToken = new AuthToken($authToken->getToken(), $user->getUuid(), new DateTimeImmutable());

        $this->tokenRepository->save($expiredAuthToken);
        $this->logger->info("LOGOUT. Token expired from logout: [$token]");
        return new SuccessfulResponse([]);
    }
}
