<?php
namespace Maxim\Postsystem\Repositories\LikeRepositories;

use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeAlreadyExist;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFound;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use PDO;
use Psr\Log\LoggerInterface;

class SqliteLikeRepository implements ILikeRepository
{
    private PDO $connection;
    private IUserRepository $userRepository;
    private IPostRepository $postRepository;
    private LoggerInterface $logger;

    public function __construct(PDO $connection ,IUserRepository $userRepository, IPostRepository $postRepository, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->logger = $logger;
    }

    private function likeExist(Like $like) :bool
    {
        $statement = $this->connection->prepare("SELECT * FROM likes
            WHERE post_uuid = :post_uuid AND author_uuid = :author_uuid");

        $statement->execute([
            "post_uuid" => (string)$like->getPostUuid(),
            "author_uuid" => (string)$like->getAuthorUuid()
        ]);

        $result = $statement->fetch();
        if($result === false){
            return false;
        }

        return true;
    }

    //Добавление лайка в БД
    public function save(Like $like): void
    {
        //Проверяем, был ли поставлен уже лайк
        if($this->likeExist($like)){
            throw new LikeAlreadyExist("Like already exist.");
        }

        $statement = $this->connection->prepare("INSERT INTO likes (uuid, post_uuid, author_uuid)
         VALUES (:uuid, :post_uuid, :author_uuid);");

        $statement->execute([
            "uuid" => (string)$like->getUuid(),
            "post_uuid" => (string)$like->getPostUuid(),
            "author_uuid" => (string)$like->getAuthorUuid()
        ]);
    }

    //Извлечение лайков к посту
    public function getByPost(UUID $postUuid): array
    {
        $likes = [];
        $statement = $this->connection->prepare("SELECT * FROM likes WHERE post_uuid = :post_uuid;");
        $statement->execute(["post_uuid" => (string)$postUuid]);
        
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $like) {
            $uuid = new UUID($like["uuid"]);
            //Пост
            $postUuid = new UUID($like["post_uuid"]);
            //Автор
            $authorUuid = new UUID(($like["author_uuid"]));

            $likes[] = new Like($uuid, $postUuid, $authorUuid);
        }

        return $likes;
    }

    //Извлечение по uuid
    public function getByUUID(UUID $uuid): Like
    {
        $statement = $this->connection->prepare("SELECT * FROM likes WHERE uuid = :uuid;");
        $statement->execute(["uuid" => (string)$uuid]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result === false){
            $message = "Like not found by uuid: " . $uuid;
            $this->logger->warning($message);
            throw new LikeNotFound($message);
        }

        $uuid = new UUID($result["uuid"]);
        //Пост
        $postUuid = new UUID($result["post_uuid"]);
        //Автор
        $authorUuid = new UUID(($result["author_uuid"]));

        return new Like($uuid, $postUuid, $authorUuid);
        
    }

    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare("DELETE FROM likes WHERE uuid = :uuid;");
        $statement->execute(["uuid" => (string)$uuid]);
    }


}