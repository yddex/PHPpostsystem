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
use Psr\Log\LoggerInterface;

class SqliteCommentRepository implements ICommentRepository
{
    private PDO $connection;
    private IUserRepository $userRepository;
    private IPostRepository $postRepository;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, IUserRepository $userRepository, IPostRepository $postRepository, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->logger = $logger;
    }


    //Сохранение комментария в таблицу
    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare("INSERT INTO comments (uuid, post_uuid, author_uuid, text)
            VALUES (:uuid, :post_uuid, :author_uuid, :text)");

        $uuid = (string)$comment->getUuid();

        $statement->execute([
            "uuid" => (string)$uuid,
            "post_uuid" => (string)$comment->getPost()->getUuid(),
            "author_uuid" => (string)$comment->getAuthor()->getUuid(),
            "text" => $comment->getText()
        ]);

        $this->logger->info("New comment create. UUID: $uuid");
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
        try{
            return $this->getCommentFromStatement($statement);

        }catch(CommentNotFoundException $e){
            $message = "Comment not found. UUID:" . (string)$uuid;
            $this->logger->warning($message);
            throw new CommentNotFoundException($message);
        }

    }
    

    //Извлечение всех комментариев к посту
    public function getByPost(Post $post): array
    {
        $statement = $this->connection->prepare("SELECT * FROM comments WHERE post_uuid LIKE :post_uuid");
        $statement->execute(["post_uuid" => (string)$post->getUuid()]);
        try{
            return $this->getAllCommentsFromStatement($statement);

        }catch(CommentNotFoundException $e){
            $message = "Comment not found by post. UUID:" . (string)$post->getUuid();
            $this->logger->warning($message);
            throw new CommentNotFoundException($message);
        }
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