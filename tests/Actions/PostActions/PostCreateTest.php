<?php
namespace Maxim\Postsystem\UnitTests\Actions\PostActions;

use DateTimeImmutable;
use Maxim\Postsystem\Blog\AuthToken;
use Maxim\Postsystem\Blog\Post;
use PHPUnit\Framework\TestCase;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\PostsActions\PostCreate;
use Maxim\Postsystem\Http\Auth\BearerTokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UnitTests\DummyLogger\DummyLogger;
use Maxim\Postsystem\UnitTests\DummyTokenRepository\DummyTokenRepository;
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
        $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";
        //создаем запрос, и передаем в него данные для создания поста
        $request = new Request([],["HTTP_AUTHORIZATION" => "Bearer $token"],'{"title":"title","text":"text"}');

        //создаем стаб репозитория и передаем автора поста
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", "password")
        ]);
        $tokenRepository = new DummyTokenRepository([new AuthToken($token, new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), (new DateTimeImmutable())->modify('+2 day'))]);
        $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);
        //стаб репозитория с постами
        $postRepository = $this->postsRepository();

        //создаем действие
        $action = new PostCreate($postRepository, $userAuthentication, new DummyLogger());
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
    //если токен не верен
    public function testItReturnErrorResponseIfTokenBad() :void
    {   
         $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";
         //создаем запрос, и передаем в него данные для создания поста с неверным uuid автора
         $request = new Request([],["HTTP_AUTHORIZATION" => "Bearer 60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6c"],'{"title":"title","text":"text"}');

         //создаем стаб репозитория и передаем автора поста
         $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", "password")
        ]);

         $tokenRepository = new DummyTokenRepository([new AuthToken($token, new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), (new DateTimeImmutable())->modify('+2 day'))]);
         $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);
 
         //стаб репозитория с постами
         $postRepository = $this->postsRepository();

         $action = new PostCreate($postRepository, $userAuthentication, new DummyLogger());

         $response = $action->handle($request);

         $this->assertInstanceOf(ErrorResponse::class, $response);

         $this->expectOutputString('{"success":false,"reason":"Bad token: [60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6c]"}');

         $response->send();
    }

     

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfSendDataNotFull() :void
    {   
        $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";
        //создаем запрос, и передаем в него данные для создания поста
        $request = new Request([],["HTTP_AUTHORIZATION" => "Bearer $token"],'{"text":"text"}');

        //создаем стаб репозитория
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", "password")
        ]);

        $tokenRepository = new DummyTokenRepository([new AuthToken($token, new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), (new DateTimeImmutable())->modify('+2 day'))]);
        $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);

        //стаб репозитория с постами
        $postRepository = $this->postsRepository();
        
        //создаем действие
        $action = new PostCreate($postRepository, $userAuthentication, new DummyLogger);
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