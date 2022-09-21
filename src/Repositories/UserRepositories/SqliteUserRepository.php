<?php
namespace Maxim\Postsystem\Repositories\UserRepositories;

use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserLoginTakenException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUserRepository implements IUserRepository
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
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
        $password = $result["password"];
        $login = $result["login"];
        return new User($uuid, $name, $login, $password);
    }

    //поиск по UUID
    public function getByUUID(UUID $uuid): User
    {
        $statement = $this->connection->prepare("SELECT * FROM users WHERE uuid LIKE :uuid");
        $statement->execute(["uuid" => (string)$uuid]);

        try{
            return $this->getUserStatement($statement);

        }catch(UserNotFoundException){
            $message = "User not found by uuid: " . $uuid;
            $this->logger->warning($message);
            throw new UserNotFoundException($message);
        }
    }

    //поиск по логину
    public function getByLogin(string $login): User
    {
        $statement = $this->connection->prepare("SELECT * FROM users WHERE login LIKE :login");
        $statement->execute(["login" => $login]);

        try{
            return $this->getUserStatement($statement);

        }catch(UserNotFoundException){
            $message = "User not found by login: " . $login;
            $this->logger->warning($message);
            throw new UserNotFoundException($message);
        }  
    }

    //Сохранение в дб
    public function save(User $user) :void
    {   
        if($this->includeLogin($user->getLogin())){
            throw new UserLoginTakenException("This login is already taken: " . $user->getLogin());
        }

        $statement = $this->connection->prepare("INSERT INTO users (uuid, name, surname, login, password)
        VALUES (:uuid, :name, :surname, :login, :password);");
        $name = $user->getName();

        $statement->execute([
            "uuid" => (string)$user->getUuid(),
            "name" => $name->getName(),
            "surname" => $name->getSurname(),
            "login" => $user->getLogin(),
            "password" => $user->getHashPassword()
        ]);

        $this->logger->info("Created new user. UUID: " . (string)$user->getUuid() );
    }

}