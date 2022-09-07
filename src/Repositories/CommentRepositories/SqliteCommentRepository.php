<?php
namespace Maxim\Postsystem\Repositories\CommentRepositories;

use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\CommentNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use PDO;
use PDOStatement;

class SqliteCommentRepository implements ICommentRepository
{
    private PDO $connection;
    private IUserRepository $userRepository;
    private IPostRepository $postRepository;

    public function __construct(PDO $connection, IUserRepository $userRepository, IPostRepository $postRepository)
    {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }


    //Сохранение комментария в таблицу
    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare("INSERT INTO comments (uuid, post_uuid, author_uuid, text)
            VALUES (:uuid, :post_uuid, :author_uuid, :text)");

        $statement->execute([
            "uuid" => (string)$comment->getUuid(),
            "post_uuid" => (string)$comment->getPost()->getUuid(),
            "author_uuid" => (string)$comment->getAuthor()->getUuid(),
            "text" => $comment->getText()
        ]);
    }

    //Получение комментария из PDOStatement
    private function getCommentFromStatement(PDOStatement $statement) :Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result === false)
        {
            throw new CommentNotFoundException("Comment not found");
        }

        $uuid = new UUID($result["uuid"]);
        $author = $this->userRepository->getByUUID(new UUID($result["author_uuid"]));
        $post = $this->postRepository->getByUUID(new UUID($result["post_uuid"]));
        $text = $result["text"];
        return new Comment($uuid, $author, $post, $text);

    }
     //Получение массива комментариев из PDOStatement
    private function getAllCommentsFromStatement(PDOStatement $statement) :array
    {
        $comments = [];
        while($statement !== false){
            try{
                 $comments[] = $this->getCommentFromStatement($statement);

            }catch(CommentNotFoundException $exception){
                return $comments;
            }
        }
        return $comments;
    }


    //Извлечение комментария по UUID
    public function getByUUID(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare("SELECT * FROM comments WHERE uuid LIKE :uuid");
        $statement->execute(["uuid" => (string)$uuid]);
        return $this->getCommentFromStatement($statement);
    }
    

    //Извлечение всех комментариев к посту
    public function getByPost(Post $post): array
    {
        $statement = $this->connection->prepare("SELECT * FROM comments WHERE post_uuid LIKE :post_uuid");
        $statement->execute(["post_uuid" => (string)$post->getUuid()]);
        return $this->getAllCommentsFromStatement($statement);
    }

    //удаление комментария из дб
    public function delete(Comment $comment) :void
    {
        $statement = $this->connection->prepare("DELETE FROM comments WHERE uuid LIKE :uuid");
        $statement->execute(["uuid" => (string)$comment->getUuid()]);
    }

    
    // public function deleteAllByPost(Post $post) :void
    // {
    //     $statement = $this->connection->prepare("DELETE FROM comments WHERE post_uuid LIKE :post_uuid");
    //     $statement->execute(["post_uuid" => (string)$post->getUuid()]);
    // }
}