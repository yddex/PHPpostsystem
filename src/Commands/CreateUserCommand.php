<?php
namespace Maxim\Postsystem\Commands;

use Maxim\Postsystem\Exceptions\ComandExceptions\CommandException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;

class CreateUserCommand
{  
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(Arguments $arguments) :void
    {   

        $name = new Name($arguments->get("name"), $arguments->get("surname"));
        $login = $arguments->get("login");
        if($this->userExists($login))
        {
            throw new CommandException("Login already is taken: $login");
        }
        
        $this->userRepository->save(new User(UUID::random(), $name, $login));
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