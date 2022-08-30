<?php

namespace Maxim\Postsystem\Blog;

use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\UUID;

class Post
{
    private UUID $uuid;
    private User $author;
    private string $title;
    private string $text;
    public function __construct(UUID $uuid, User $author, string $title, string $text)
    {
        $this->uuid = $uuid;
        $this->author = $author;
        $this->title = $title;
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
     * Get the value of title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
        return "Пост №$this->uuid" . PHP_EOL . 
            "Автор: " . $this->author->getName() . PHP_EOL .
            "Оглавление: $this->title" . PHP_EOL .
            "Текст: $this->text";
    }




}