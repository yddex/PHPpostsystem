<?php
namespace Maxim\Postsystem\UnitTests\RepositorIes\LikeRepositories;

use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Repositories\LikeRepositories\SqliteLikeRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Maxim\Postsystem\UnitTests\DummyLogger\DummyLogger;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class SqliteLikeRepositoryTest extends TestCase
{

    //Проверяем сохранение лайка в БД
    public function testItSaveLikeInDatabase() :void
    {
        //Создание стабов
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);
        $postRepositoryStub = $this->createStub(IPostRepository::class);
        //Настройка стабов
        $statementMock->method('fetch')->willReturn(false);
        $statementMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [
                   $this->equalTo(
                    [
                        "post_uuid" => "5cb259d2-4ee3-4737-9be3-3703e8a88c55",
                        "author_uuid" => "03b08b64-3575-4479-baf4-a51c94785b3a"
                    ])
                ],
                [
                    $this->equalTo(
                    [
                        "uuid" => "fb40d053-026c-4e64-83fe-0d9882cd3464",
                        "post_uuid" => "5cb259d2-4ee3-4737-9be3-3703e8a88c55",
                        "author_uuid" => "03b08b64-3575-4479-baf4-a51c94785b3a"
                    ])
                ]
            );
        $connectionStub->method('prepare')->willReturn($statementMock);

        //Создаем требуемые сущности
        $postUuid = new UUID("5cb259d2-4ee3-4737-9be3-3703e8a88c55");
        $authorUuid = new UUID("03b08b64-3575-4479-baf4-a51c94785b3a");
    
        $like = new Like(
            new UUID("fb40d053-026c-4e64-83fe-0d9882cd3464"),
            $postUuid,
            $authorUuid
        );
        
        $likeRepository = new SqliteLikeRepository($connectionStub, $userRepositoryStub, $postRepositoryStub, new DummyLogger());
        $likeRepository->save($like);

    }

    //Проверяем извлечение массива обьектов класса like по посту
    public function testItGetByPost() :void
    {
        //Создание стабов
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $userRepositoryStub = $this->createStub(IUserRepository::class);
        $postRepositoryStub = $this->createStub(IPostRepository::class);
        //Настройка стабов

        $statementMock->method('fetchAll')->willReturn([
           [ 
            "uuid" => "fb40d053-026c-4e64-83fe-0d9882cd3464",
            "post_uuid" => "5cb259d2-4ee3-4737-9be3-3703e8a88c55",
            "author_uuid" => "03b08b64-3575-4479-baf4-a51c94785b3a"
            ]
        ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        //Создаем требуемые сущности
        
        $postUuid = new UUID("5cb259d2-4ee3-4737-9be3-3703e8a88c55");
        $authorUuid =    new UUID("03b08b64-3575-4479-baf4-a51c94785b3a");
    
        $like = new Like(
            new UUID("fb40d053-026c-4e64-83fe-0d9882cd3464"),
            $postUuid,
            $authorUuid
        );
        
        $likeRepository = new SqliteLikeRepository($connectionStub, $userRepositoryStub, $postRepositoryStub, new DummyLogger());
        $likes = $likeRepository->getByPost($postUuid);

        $this->assertInstanceOf(Like::class, $likes[0]);
        $this->assertSame("fb40d053-026c-4e64-83fe-0d9882cd3464", (string)$likes[0]->getUuid());

    }
}