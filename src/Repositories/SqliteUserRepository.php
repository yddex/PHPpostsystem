<?php
namespace Maxim\Postsystem\Repositories;

use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserLoginTakenException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\UUID;
use PDO;
use PDOStatement;

class SqliteUserRepository implements IUserRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }



    private function includeLogin(string $login) :bool
    {
        $statement = $this->connection->prepare("SELECT * FROM users WHERE login LIKE :login");
        $statement->execute(["login" => $login]);
        $result = $statement->fetch();
        if($result === false){
            return false;
        }
        return true;
    }


    private function getUserStatement(PDOStatement $statement) :User
    {
        $result = $statement->fetch();
        
        if($result === false){
            throw new UserNotFoundException("User not found");
        }

        $uuid = new UUID($result["uuid"]);
        $name = new Name($result["name"], $result["surname"]);
        return new User($uuid, $name, $result["login"]);
    }

    //поиск по UUID
    public function getByUUID(UUID $uuid): User
    {
        $statement = $this->connection->prepare("SELECT * FROM users WHERE uuid LIKE :uuid");
        $statement->execute(["uuid" => (string)$uuid]);
        return $this->getUserStatement($statement);
    }

    //поиск по логину
    public function getByLogin(string $login): User
    {
        $statement = $this->connection->prepare("SELECT * FROM users WHERE login LIKE :login");
        $statement->execute(["login" => $login]);
        return $this->getUserStatement($statement);
    }

    //Сохранение в дб
    public function save(User $user) :void
    {   
        if($this->includeLogin($user->getLogin())){
            throw new UserLoginTakenException("this login is already taken");
        }

        $statement = $this->connection->prepare("INSERT INTO users (uuid, name, surname, login)
        VALUES (:uuid, :name, :surname, :login);");
        $name = $user->getName();

        $statement->execute([
            "uuid" => (string)$user->getUuid(),
            "name" => $name->getName(),
            "surname" => $name->getSurname(),
            "login" => $user->getLogin()
        ]);
    }
}