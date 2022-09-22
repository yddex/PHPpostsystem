<?php

namespace Maxim\Postsystem\UnitTests\Actions\LikeActions;

use DateTimeImmutable;
use Maxim\Postsystem\Blog\AuthToken;
use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeAlreadyExist;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFound;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\LikeActions\LikeCreate;
use Maxim\Postsystem\Http\Auth\BearerTokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UnitTests\DummyTokenRepository\DummyTokenRepository;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\TestCase;

class LikeCreateTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnSuccessfulResponse(): void
    {
        $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";

        $request = new Request([], ["HTTP_AUTHORIZATION" => "Bearer $token"], '{"post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}');

        $likeRepositoryStub = $this->likeRepository([]);
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", "password")
        ]);

        $tokenRepository = new DummyTokenRepository([new AuthToken($token, new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), (new DateTimeImmutable())->modify('+2 day'))]);
        $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);
        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub, $userAuthentication);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data) {
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode["data"]["uuid"] = "fb40d053-026c-4e64-83fe-0d9882cd3464";

            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"fb40d053-026c-4e64-83fe-0d9882cd3464"}}');


        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfLikeAlreadyExist(): void
    {
        $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";

        $request = new Request([], ["HTTP_AUTHORIZATION" => "Bearer $token"], '{"post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}');

        $likeRepositoryStub = $this->likeRepository([
            new Like(
                new UUID("fb40d053-026c-4e64-83fe-0d9882cd3464"),
                new UUID("351739ab-fc33-49ae-a62d-b606b7038c87"),
                new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa")
            )
        ]);
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", "password")
        ]);

        $tokenRepository = new DummyTokenRepository([new AuthToken($token, new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), (new DateTimeImmutable())->modify('+2 day'))]);
        $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);
        //Создаем действие
        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub, $userAuthentication);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);


        $this->expectOutputString('{"success":false,"reason":"Like already exist."}');


        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfUuidMailformed(): void
    {       
        $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";

        $request = new Request([], ["HTTP_AUTHORIZATION" => "Bearer $token"], '{"post_uuid":"351739ab-fc33-49ae"}');
        $likeRepositoryStub = $this->likeRepository([]);

        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill", "password")
        ]);
        $tokenRepository = new DummyTokenRepository([new AuthToken($token, new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), (new DateTimeImmutable())->modify('+2 day'))]);
        $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);

        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub, $userAuthentication);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);


        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 351739ab-fc33-49ae"}');


        $response->send();
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfTransferedNotFullData(): void
    {   
        $token = "60d4ccdb841f09c0e519c55aeea90b88b45611170bc90cc6cda989225531ae42b9023e42a6baa169";
        $request = new Request([], [], '{"post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}');

        $likeRepositoryStub = $this->likeRepository([]);

        $userRepository = $this->usersRepository([]);
        $tokenRepository = new DummyTokenRepository([]);
        $userAuthentication = new BearerTokenAuthentication($tokenRepository, $userRepository);

        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub, $userAuthentication);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);


        $this->expectOutputString('{"success":false,"reason":"No such header in the request: Authorization"}');


        $response->send();
    }



    private function likeRepository(array $likes): object
    {
        return new class($likes) implements ILikeRepository
        {

            private array $likes;
            public function __construct(array $likes)
            {
                $this->likes = $likes;
            }


            public function save(Like $like): void
            {
                foreach ($this->likes as $likeObj) {
                    //Проверяем на единичность лайка в репозитории
                    if (
                        (string)$likeObj->getAuthorUuid() === (string)$like->getAuthorUuid() &&
                        (string)$likeObj->getPostUuid() === (string)$like->getPostUuid()
                    ) {
                        throw new LikeAlreadyExist("Like already exist.");
                    }
                }
            }

            public function getByPost(UUID $post): array
            {
                throw new LikeNotFoundException("Not found");
            }

            public function getByUUID(UUID $uuid): Like
            {
                throw new LikeNotFoundException("Not found");
            }

            public function delete(UUID $uuid): void
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
            public function __construct(array $users)
            {
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
                throw new UserNotFoundException("User not found by uuid: " . $uuid);
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
