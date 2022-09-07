<?php

namespace Maxim\Postsystem\UnitTests\Actions\UserActions;

use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\UserActions\UserFindByLogin;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\TestCase;

class UserFindByLoginActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //проверяем, что будет возвращен ответ с ошибкой
    //если не передать параметр login
    public function testItReturnsErrorResponseIfNoLoginProvided(): void
    {
        //создаем обьект запроса 
        $request = new Request([], [], "");
        // Создаём стаб репозитория пользователей
        $userRepository = $this->usersRepository([]);
        //создаем обьект действия
        $action = new UserFindByLogin($userRepository);
        //запускаем действие
        $response =  $action->handle($request);

        //проверяем ответ, ожидая неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);
        //ожидаемые выходные данные
        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: login"}');

        //отправляем данные в поток вывода
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен ответ с ошибкой
    //если пользователь не найден
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request(['login' => 'ivan'], [], "");

        $usersRepository = $this->usersRepository([]);

        $action = new UserFindByLogin($usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен успешный ответ
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request(["login" => "bill"], [], "");

        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "name"), "bill")
        ]);

        $action = new UserFindByLogin($userRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa","name":"name","surname":"name","login":"bill"}}');

        $response->send();
    }



    //стаб
    private function usersRepository(array $users): IUserRepository
    {

        return new class($users) implements IUserRepository
        {
            private array $users;

            public function __construct(array $users) 
            {
                    $this->users = $users;
            }
            public function save(User $user): void
            {
            }

            public function getByUUID(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByLogin(string $login): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $login === $user->getLogin()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Not found");
            }
        };
    }
}
