<?php

namespace Maxim\Postsystem\Blog;

use Maxim\Postsystem\Person\User;

class Comment
{
    private int $id;
    private User $author;
    private Post $post;
    private string $text;
    
    public function __construct(int $id, User $author, Post $post, string $text)
    {
        $this->id = $id;
        $this->author = $author;
        $this->post = $post;
        $this->text = $text;
    }

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of author
     *
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * Get the value of post
     *
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * Get the value of text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function __toString()
    {
        return "Комментарий к посту №" . $this->post->getId() . PHP_EOL .
                "Автор: " . $this->author->getName() . PHP_EOL . 
                "Текст: " . $this->text;
    }
}