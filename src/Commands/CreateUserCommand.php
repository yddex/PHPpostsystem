<?php
namespace Maxim\Postsystem\Commands;

use Maxim\Postsystem\Exceptions\ComandExceptions\CommandException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use Psr\Log\LoggerInterface;

class CreateUserCommand
{  
    private IUserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(IUserRepository $userRepository, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function handle(Arguments $arguments) :void
    {   

        $name = new Name($arguments->get("name"), $arguments->get("surname"));
        $login = $arguments->get("login");
        $password = $arguments->get("password");
        if($this->userExists($login))
        {
            throw new CommandException("Login already is taken: $login");
        }
        
        $this->userRepository->save(User::createFrom($name, $login, $password));

        $this->logger->info("User created. Login:" . $login);
        

    }

    private function userExists(string $login) :bool
    {
        try {
            $this->userRepository->getByLogin($login);
        } catch (UserNotFoundException) {
            return false;
        }

        return true;
    }
}