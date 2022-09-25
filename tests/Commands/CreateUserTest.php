<?php

namespace Maxim\Postsystem\UnitTests\Commands;

use Maxim\Postsystem\UUID;
use Maxim\Postsystem\Blog\User;
use PHPUnit\Framework\TestCase;
use Maxim\Postsystem\Commands\Users\CreateUser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;

class CreateUserTest extends TestCase
{
    public function testItRequiresPassword(): void
    {
        // Тестируем новую команду
        $command = new CreateUser(
            $this->usersRepository([]),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "password").'
        );

        $command->run(

            new ArrayInput([
                'name' => 'Ivan',
                'surname' => 'surname',
                'login' => 'Ivan',
               ]),
            
            new NullOutput()
        );
    }

    public function testItRequiresLogin(): void
    {
        // Тестируем новую команду
        $command = new CreateUser(
            $this->usersRepository([]),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "login").'
        );

        $command->run(

            new ArrayInput([
                'name' => 'name',
                'surname' => 'surname',
                'password' => 'pass'
               ]),
            
            new NullOutput()
        );
    }

    public function testItRequiresName(): void
    {
        // Тестируем новую команду
        $command = new CreateUser(
            $this->usersRepository([]),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "name").'
        );

        $command->run(

            new ArrayInput([
                'surname' => 'surname',
                'login' => 'login',
                'password' => 'pass'
               ]),
            
            new NullOutput()
        );
    }


    public function testItRequiresSurname(): void
    {
        // Тестируем новую команду
        $command = new CreateUser(
            $this->usersRepository([]),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "surname").'
        );

        $command->run(
            new ArrayInput([
                'name' => 'name',
                'login' => 'login',
                'password' => 'pass'
               ]),
            
            new NullOutput()
        );
    }

    public function testItSavesUserInRepository() :void
    {
        $userRepository = $this->usersRepository([]);
        $command = new CreateUser($userRepository);


        $command->run(
            new ArrayInput([
                'name' => 'name',
                'surname' => 'surname',
                'login' => 'login',
                'password' => 'pass'
               ]),
            
            new NullOutput()
        );

        $this->assertTrue($userRepository->getCalled());
    }

    


    //стаб репозитория пользователей
    private function usersRepository(array $users): IUserRepository
    {

        return new class($users) implements IUserRepository
        {
            private array $users;
            private bool $called = false;
            public function __construct(array $users)
            {
                $this->users = $users;
            }

            public function save(User $user): void
            {
                $this->called = true;
            }

            public function getByUUID(UUID $uuid): User
            {
                
                throw new UserNotFoundException("User not found by uuid:" . $uuid);
            }

            public function getByLogin(string $login): User
            {
                
                throw new UserNotFoundException("Not found");
            }

            /**
             * Get the value of called
             *
             * @return bool
             */
            public function getCalled(): bool
            {
                 return $this->called;
            }
        };
    }
}
