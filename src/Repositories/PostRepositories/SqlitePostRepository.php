<?php

namespace Maxim\Postsystem\Repositories\PostRepositories;

use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Maxim\Postsystem\UUID;
use PDO;
use PDOStatement;

class SqlitePostRepository implements IPostRepository
{
    private PDO $connection;
    private IUserRepository $userRepository;

    public function __construct(PDO $connection, IUserRepository $userRepository)
    {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
    }

    //запись в таблицу
    public function save(Post $post): void
    {
        $statement = $this->connection->prepare("INSERT INTO posts (uuid, author_uuid, title, text)
            VALUES (:uuid, :author_uuid, :title, :text);");
        
        $statement->execute([
            "uuid" => (string)$post->getUuid(),
            "author_uuid" => (string)$post->getAuthor()->getUuid(),
            "title" => $post->getTitle(),
            "text" => $post->getText()
        ]);
    }

    
    private function getPostFromStatement(PDOStatement $statement) :Post
    {
        $result = $statement->fetch();
        if($result === false){
            throw new PostNotFoundException("Post not found");
        }
   
        $uuid = new UUID($result["uuid"]);
        $author_uuid = new UUID($result["author_uuid"]);
        $author = $this->userRepository->getByUUID($author_uuid);
        $title = $result["title"];
        $text = $result["text"];

        return new Post($uuid, $author, $title, $text);
    }

    private function getAllPostsFromStatement(PDOStatement $statement) :array
    {
        $posts = [];
        while($statement !== false){
            try{
                $posts[] = $this->getPostFromStatement($statement);
            }catch(PostNotFoundException $exception){

                return $posts;
            }
        }
        return $posts;
    }

    //получение массива обьектов всех постов из таблицы
    public function getAll(): array
    {
        
        $statement = $this->connection->prepare("SELECT * FROM posts");
        $statement->execute();


        return $this->getAllPostsFromStatement($statement);
    }

    //извлечение по UUID поста
    public function getByUUID(UUID $uuid): Post
    {
        $statement = $this->connection->prepare("SELECT * FROM posts WHERE uuid LIKE :uuid");
        $statement->execute(["uuid" => (string)$uuid]);
        try{
            return $this->getPostFromStatement($statement);
        }catch(PostNotFoundException $e){
            throw new PostNotFoundException("Post not found. UUID: " . (string)$uuid);
        }

    }

    //Извлечение всех постов пользователя
    public function getAllByAuthor(User $author): array
    {
        $statement = $this->connection->prepare("SELECT * FROM posts WHERE author_uuid LIKE :author_uuid");
        $statement->execute(["author_uuid" => (string)$author->getUuid()]);


        return $this->getAllPostsFromStatement($statement);
    }
}
