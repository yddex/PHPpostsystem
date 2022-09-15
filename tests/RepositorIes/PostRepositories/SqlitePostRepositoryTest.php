<?php
namespace Maxim\Postsystem\UnitTests\RepositorIes\PostRepositories;


use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Maxim\Postsystem\UnitTests\DummyLogger\DummyLogger;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class SqlitePostRepositoryTest extends TestCase
{
    public function testItSavesPostInDatabase() :void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                "uuid" => "fb40d053-026c-4e64-83fe-0d9882cd3464",
                "author_uuid" => "03b08b64-3575-4479-baf4-a51c94785b3a",
                "title" => "test_title",
                "text" => "test_text"
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $user = new User(new UUID("03b08b64-3575-4479-baf4-a51c94785b3a"), new Name("Name", "Surname"), "login");
        $post = new Post(new UUID("fb40d053-026c-4e64-83fe-0d9882cd3464"), $user, "test_title", "test_text");

        $postRepository = new SqlitePostRepository($connectionStub, $userRepositoryStub, new DummyLogger());
        $postRepository->save($post);
    }

    public function testItGetPostByUuid() :void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);

        $statementStub->method('fetch')->willReturn([
            "uuid" => "fb40d053-026c-4e64-83fe-0d9882cd3464",
            "author_uuid" => "03b08b64-3575-4479-baf4-a51c94785b3a",
            "title" => "test_title",
            "text" =>  "test_text"
        ]);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $user = new User(new UUID("03b08b64-3575-4479-baf4-a51c94785b3a"), new Name("Name", "Surname"), "login");
        $userRepositoryStub->method('getByUUID')->willReturn($user);


        $postRepository = new SqlitePostRepository($connectionStub, $userRepositoryStub, new DummyLogger());
        $returnedPost = $postRepository->getByUUID(new UUID("fb40d053-026c-4e64-83fe-0d9882cd3464"));
        $this->assertSame("fb40d053-026c-4e64-83fe-0d9882cd3464", (string)$returnedPost->getUuid());
    }

    public function testItThrowExceptionWhenPostNotFound() :void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage("Post not found. UUID: 5cb259d2-4ee3-4737-9be3-3703e8a88c55");

        $postRepository = new SqlitePostRepository($connectionStub, $userRepositoryStub, new DummyLogger());
        $postRepository->getByUUID(new UUID("5cb259d2-4ee3-4737-9be3-3703e8a88c55"));
    }

}