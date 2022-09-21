<?php
namespace Maxim\Postsystem\UnitTests\RepositorIes\CommentRepositories;


use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\CommentNotFoundException;
use Maxim\Postsystem\Repositories\CommentRepositories\SqliteCommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UnitTests\DummyLogger\DummyLogger;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class SqliteCommentRepositoryTest extends TestCase
{

    public function testItSavesCommentInDatabase() :void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $postRepositoryStub = $this->createStub(IPostRepository::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                "uuid" => "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa",
                "post_uuid" => "7de1b6b2-8204-42f0-bff7-5cd10138dffd",
                "author_uuid" => "f832afbe-5d70-418d-9f60-23eec4112619",
                "text" => "test_comment_text"
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $password = hash("sha256", "password" . "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa");
        $user = new User(new UUID("f832afbe-5d70-418d-9f60-23eec4112619"), new Name("name", "name"), "login", $password);
        $post = new Post(new UUID("7de1b6b2-8204-42f0-bff7-5cd10138dffd"), $user, "title", "text");
        $comment = new Comment(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"), $user, $post, "test_comment_text");

        $commentRepository = new SqliteCommentRepository($connectionStub, $userRepositoryStub, $postRepositoryStub, new DummyLogger());
        $commentRepository->save($comment);
    }



    public function testItGetCommentByUuid() :void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);
        $postRepositoryStub = $this->createStub(IPostRepository::class);

        $statementStub->method('fetch')->willReturn([
            "uuid" => "2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa",
            "author_uuid" => "7de1b6b2-8204-42f0-bff7-5cd10138dffd",
            "post_uuid" => "f832afbe-5d70-418d-9f60-23eec4112619",
            "text" => "test_comment_text"
        ]);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $commentRepository = new SqliteCommentRepository($connectionStub, $userRepositoryStub, $postRepositoryStub, new DummyLogger());
        $returnedComment = $commentRepository->getByUUID(new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"));

        $this->assertSame("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa", (string)$returnedComment->getUuid());
    }



    public function testItThrowExceptionWhenCommentNotFound() :void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);
        $postRepositoryStub = $this->createStub(IPostRepository::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage("Comment not found");
    
        $commentRepository = new SqliteCommentRepository($connectionStub, $userRepositoryStub, $postRepositoryStub, new DummyLogger());
        $commentRepository->getByUUID(new UUID("7de1b6b2-8204-42f0-bff7-5cd10138dffd"));
    }
}