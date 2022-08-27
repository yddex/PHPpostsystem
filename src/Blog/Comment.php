<?php

namespace Maxim\Postsystem\Blog;

use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\UUID;

class Comment
{
    private UUID $uuid;
    private User $author;
    private Post $post;
    private string $text;
    
    public function __construct(UUID $uuid, User $author, Post $post, string $text)
    {
        $this->uuid = $uuid;
        $this->author = $author;
        $this->post = $post;
        $this->text = $text;
    }


      /**
     * Get the value of uuid
     *
     * @return UUID
     */
    public function getUuid(): UUID
    {
        return $this->uuid;
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
        return "Комментарий к посту №" . $this->post->getUuid() . PHP_EOL .
                "Автор: " . $this->author->getName() . PHP_EOL . 
                "Текст: " . $this->text;
    }

  
}