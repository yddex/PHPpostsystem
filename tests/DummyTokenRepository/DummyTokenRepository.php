<?php
namespace Maxim\Postsystem\UnitTests\DummyTokenRepository;


use Maxim\Postsystem\Blog\AuthToken;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\AuthTokenNotFound;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\AuthTokenNotFoundException;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\IAuthTokenRepository;

class DummyTokenRepository implements IAuthTokenRepository
{
    private array $tokens = [];

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function get(string $token): AuthToken
    {
        foreach ($this->tokens as $authToken) {
            if($authToken->getToken() === $token){
                return $authToken;
            }
        }

        throw new AuthTokenNotFoundException("Token not found");
    }

    public function save(AuthToken $token): void
    {
        
    }
}
