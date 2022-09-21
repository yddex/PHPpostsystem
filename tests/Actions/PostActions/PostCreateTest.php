<?php
namespace Maxim\Postsystem\UnitTests\Actions\PostActions;

use Maxim\Postsystem\Blog\Post;
use PHPUnit\Framework\TestCase;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\PostsActions\PostCreate;
use Maxim\Postsystem\Http\Actions\UserActions\UserFindByLogin;
use Maxim\Postsystem\Http\Auth\JsonBodyUuidIdentification;
use Maxim\Postsystem\Http\Auth\PasswordAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UnitTests\DummyLogger\DummyLogger;
use Maxim\Postsystem\UUID;


class PostCreateTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен успешный ответ
    public function testItReturnSuccessfulResponse():void
    {
        //создаем запрос, и передаем в него данные для создания поста
        $request = new Request([],[],'{"login":"bill","password":"password","title":"title","text":"text"}');

        //создаем стаб репозитория и передаем автора поста
        $password = hash("sha256", "password" . "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa");
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", $password)
        ]);
        $userIdentification = new PasswordAuthentication($userRepository);
        //стаб репозитория с постами
        $postRepository = $this->postsRepository();

        //создаем действие
        $action = new PostCreate($postRepository, $userIdentification, new DummyLogger());
        //выполняем действие
        $response = $action->handle($request);
        //ожидаем успешный ответ
        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        //устанавливаем выходные данные
        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";

            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        //ожидаем выходные данные установленного формата
        $this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');
        //отправляем данные
        $response->send();
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен ответ с ошибкой
    //если пароль автора неверный
    public function testItReturnErrorResponseIfPasswordNotVerifed() :void
    {
         //создаем запрос, и передаем в него данные для создания поста с неверным uuid автора
         $request = new Request([],[],'{"login":"bill","password":"password2","title":"title","text":"text"}');

         $password = hash("sha256", "password" . "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa");
         //создаем стаб репозитория и передаем автора поста
         $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", $password)
        ]);
         $userIdentification = new PasswordAuthentication($userRepository);
 
         //стаб репозитория с постами
         $postRepository = $this->postsRepository();

         $action = new PostCreate($postRepository, $userIdentification, new DummyLogger());

         $response = $action->handle($request);

         $this->assertInstanceOf(ErrorResponse::class, $response);

         $this->expectOutputString('{"success":false,"reason":"Wrong password!"}');

         $response->send();
    }

     /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем,что будет возвращен ответ с ошибкой, если пользователь с переданным login
    //не будет найден
    public function testItReturnErrorResponseIfUserNotFoundByLogin() :void
    {
        //создаем запрос, и передаем в него данные для создания поста
        $request = new Request([],[],'{"login":"bill2","password":"password","title":"title","text":"text"}');

        //создаем стаб репозитория
        $userRepository = $this->usersRepository([]);
        $userIdentification = new PasswordAuthentication($userRepository);

        //стаб репозитория с постами
        $postRepository = $this->postsRepository();

        //создаем действие
        $action = new PostCreate($postRepository, $userIdentification, new DummyLogger());
        //выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');

        $response->send();
    }



    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfSendDataNotFull() :void
    {
        //создаем запрос, и передаем в него данные для создания поста
        $request = new Request([],[],'{"login":"bill","password":"password","text":"text"}');

        //создаем стаб репозитория
        $password = hash("sha256", "password" . "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa");
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", $password)
        ]);
        $userIdentification = new PasswordAuthentication($userRepository);

        //стаб репозитория с постами
        $postRepository = $this->postsRepository();
        
        //создаем действие
        $action = new PostCreate($postRepository, $userIdentification, new DummyLogger);
        //выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"No such field: title"}');

        $response->send();
    }





    //стаб репозитория с постами
    private function postsRepository(): IPostRepository
    {
        return new class() implements IPostRepository {
            private bool $called = false;

            public function __construct()
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }   

            public function getAll(): array
            {
                throw new PostNotFoundException('Not found');
            }

            public function getByUUID(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }
            
            public function getAllByAuthor(User $author): array
            {
                throw new PostNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function delete(Post $post): void
            {
            }
        };
    }
    //стаб репозитория пользователей
    private function usersRepository(array $users): IUserRepository
    {

        return new class($users) implements IUserRepository
        {
            private array $users;
            public function __construct(array $users) {
                $this->users = $users;
            }

            public function save(User $user): void
            {
            }

            public function getByUUID(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid === (string)$user->getUuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("User not found by uuid:" . $uuid);
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