<?php
namespace Maxim\Postsystem\Repositories\LikeRepositories;

use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFound;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use PDO;

class SqliteLikeRepository implements ILikeRepository
{
    private PDO $connection;
    private IUserRepository $userRepository;
    private IPostRepository $postRepository;

    public function __construct(PDO $connection ,IUserRepository $userRepository, IPostRepository $postRepository)
    {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    //Добавление лайка в БД
    public function save(Like $like): void
    {
        $statement = $this->connection->prepare("INSERT INTO likes (uuid, post_uuid, author_uuid)
         VALUES (:uuid, :post_uuid, :author_uuid);");

        $statement->execute([
            "uuid" => (string)$like->getUuid(),
            "post_uuid" => (string)$like->getPost()->getUuid(),
            "author_uuid" => (string)$like->getAuthor()->getUuid()
        ]);
    }

    //Извлечение лайков к посту
    public function getByPost(Post $post): array
    {
        $likes = [];
        $statement = $this->connection->prepare("SELECT * FROM likes WHERE post_uuid = :post_uuid;");
        $statement->execute(["post_uuid" => (string)$post->getUuid()]);
        
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $like) {
            $uuid = new UUID($like["uuid"]);
            //Пост
            $postUuid = new UUID($like["post_uuid"]);
            $post = $this->postRepository->getByUUID($postUuid);
            //Автор
            $authorUuid = new UUID(($like["author_uuid"]));
            $author = $this->userRepository->getByUUID($authorUuid);

            $likes[] = new Like($uuid, $post, $author);
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
            throw new LikeNotFound("Like not found by uuid: " . $uuid);
        }

        $uuid = new UUID($result["uuid"]);
        //Пост
        $postUuid = new UUID($result["post_uuid"]);
        $post = $this->postRepository->getByUUID($postUuid);
        //Автор
        $authorUuid = new UUID(($result["author_uuid"]));
        $author = $this->userRepository->getByUUID($authorUuid);

        return new Like($uuid, $post, $author);
        
    }

    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare("DELETE FROM likes WHERE uuid = :uuid;");
        $statement->execute(["uuid" => (string)$uuid]);
    }


}