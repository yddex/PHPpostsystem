<?php
namespace Maxim\Postsystem\Repositories\AuthTokenRepositories;

use DateTimeImmutable;
use DateTimeInterface;
use Maxim\Postsystem\Blog\AuthToken;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\AuthTokenNotFound;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\AuthTokenNotFoundException;
use Maxim\Postsystem\UUID;
use PDO;

class SqliteAuthTokenRepository implements IAuthTokenRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(AuthToken $token): void
    {
        $statement = $this->connection->prepare("INSERT INTO tokens (token, user_uuid, expires_on) 
            VALUES (:token, :user_uuid, :expires_on)
            ON CONFLICT (token) DO UPDATE SET expires_on = :expires_on;");

        $statement->execute([
            "token" => $token->getToken(),
            "user_uuid" => (string)$token->getUserUuid(),
            "expires_on" => $token->getExpiresOn()->format(DateTimeInterface::ATOM)
        ]);
    }

    public function get(string $token): AuthToken
    {
        $statement = $this->connection->prepare("SELECT * FROM tokens WHERE token = :token;");
        $statement->execute(["token" => $token]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if($result === false){
            throw new AuthTokenNotFoundException("Cannot found token: $token");
        }

        return new AuthToken(
            $result["token"],
            new UUID($result["user_uuid"]),
            new DateTimeImmutable($result["expires_on"])
        );
    }
}