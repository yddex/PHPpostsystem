<?php
namespace Maxim\Postsystem\UnitTests\Actions\CommentActions;

use Maxim\Postsystem\Blog\Post;
use PHPUnit\Framework\TestCase;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\PostsActions\PostCreate;
use Maxim\Postsystem\Http\Actions\UserActions\UserFindByLogin;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\UUID;
use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\CommentNotFoundException;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentCreate;
use Maxim\Postsystem\Http\Auth\JsonBodyUuidIdentification;

class CommentCreateTest extends TestCase
{


     /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен успешный ответ
    public function testItReturnSuccessfulResponse() :void
    {
        $request = new Request([], [], '{"author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa", "post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87", "text":"text"}');
        //создаем стабы репозиториев
        $commentRepository = $this->commentsRepository();
        $postRepository = $this->postsRepository([
                new Post(
                    new UUID("351739ab-fc33-49ae-a62d-b606b7038c87"),
                    new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill"),
                    "title",
                    "text"
                )]);
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill")
        ]);

        $userIdentification = new JsonBodyUuidIdentification($userRepository);
        //создаем действие
        $action = new CommentCreate($commentRepository, $userIdentification, $postRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa";

            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"}}');

        $response->send();
    }




    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен ответ с ошибкой если не был передан какойлибо аргумент
    public function testItReturnErrorResponseIfTakeNotFullData() :void
    {
        $request = new Request([], [], '{"author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa", "text":"text"}');
        //создаем стабы репозиториев
        $commentRepository = $this->commentsRepository();
        $postRepository = $this->postsRepository([]);

        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill")
        ]);
        $userIdentification = new JsonBodyUuidIdentification($userRepository);
        //создаем действие
        $action = new CommentCreate($commentRepository, $userIdentification, $postRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"No such field: post_uuid"}');

        $response->send();
    }

        /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен ответ с ошибкой если пост не был найден
    public function testItReturnErrorResponseIfPostNotFound() :void
    {
        $request = new Request([], [], '{"author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa","post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87","text":"text"}');
        //создаем стабы репозиториев
        $commentRepository = $this->commentsRepository();
        $postRepository = $this->postsRepository([]);
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill")
        ]);

        $userIdentification = new JsonBodyUuidIdentification($userRepository);
        //создаем действие
        $action = new CommentCreate($commentRepository, $userIdentification, $postRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"Post not found by uuid: 351739ab-fc33-49ae-a62d-b606b7038c87"}');

        $response->send();
    }

          /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен ответ с ошибкой если пользователь не был найден
    public function testItReturnErrorResponseIfAuthorNotFound() :void
    {
        $request = new Request([], [], '{"author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa","post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87","text":"text"}');
        //создаем стабы репозиториев
        $commentRepository = $this->commentsRepository();
        $postRepository = $this->postsRepository([
            new Post(
                new UUID("351739ab-fc33-49ae-a62d-b606b7038c87"),
                new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill"),
                "title",
                "text"
        )]);
        $userRepository = $this->usersRepository([]);
        $userIdentification = new JsonBodyUuidIdentification($userRepository);
        //создаем действие
        $action = new CommentCreate($commentRepository, $userIdentification, $postRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"User not found by uuid: 2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"}');

        $response->send();
    }

            /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    //Проверяем, что будет возвращен ответ с ошибкой если uuid не верного формата
    public function testItReturnErrorResponseIfMalformedUuid() :void
    {
        $request = new Request([], [], '{"author_uuid":"2a5f9ba6-b0c2-4143-9ca0","post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87","text":"text"}');
        //создаем стабы репозиториев
        $commentRepository = $this->commentsRepository();
        $postRepository = $this->postsRepository([
            new Post(
                new UUID("351739ab-fc33-49ae-a62d-b606b7038c87"),
                new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill"),
                "title",
                "text"
        )]);
        $userRepository = $this->usersRepository([
            new User(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), new Name("name", "surname"), "bill")
        ]);
        $userIdentification = new JsonBodyUuidIdentification($userRepository);
        //создаем действие
        $action = new CommentCreate($commentRepository, $userIdentification, $postRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 2a5f9ba6-b0c2-4143-9ca0"}');

        $response->send();
    }












        //стаб репозитория с комментариями
        private function commentsRepository() :ICommentRepository
        {
            return new class() implements ICommentRepository {
                private bool $called = false;
                public function __construct()
                {
                }

                public function save(Comment $comment): void
                {
                    $this->called = true;
                }

                public function getByUUID(UUID $uuid): Comment
                {
                    throw new CommentNotFoundException("Not found");
                }

                public function getByPost(Post $post): array
                {
                    throw new CommentNotFoundException("Not found");
                }

                public function delete(Comment $comment): void
                {
                    
                }

                public function deleteAllByPost(Post $post): void
                {
                    
                }
            };
        }



        //стаб репозитория с постами
        private function postsRepository(array $posts): IPostRepository
        {
            return new class($posts) implements IPostRepository {
                
                private array $posts;
                public function __construct(array $posts)
                {
                    $this->posts = $posts;
                }
    
                public function save(Post $post): void
                {
                }   
    
                public function getAll(): array
                {
                    throw new PostNotFoundException('Not found');
                }
    
                public function getByUUID(UUID $uuid): Post
                {
                    foreach ($this->posts as $post) {
                        if ($post instanceof Post && (string)$uuid === (string)$post->getUuid()) {
                            return $post;
                        }
                    }
                    throw new PostNotFoundException('Post not found by uuid: ' . $uuid);
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