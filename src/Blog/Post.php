<?php

namespace Maxim\Postsystem\Blog;

use Maxim\Postsystem\Person\User;

class Post
{
    private int $id;
    private User $author;
    private string $title;
    private string $text;
    public function __construct(int $id, User $author, string $title, string $text)
    {
        $this->id = $id;
        $this->author = $author;
        $this->title = $title;
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
     * @return int
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
        return "Пост №$this->id" . PHP_EOL . 
            "Автор: " . $this->author->getName() . PHP_EOL .
            "Оглавление: $this->title" . PHP_EOL .
            "Текст: $this->text";
    }
}