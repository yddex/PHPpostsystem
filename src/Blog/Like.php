<?php
namespace Maxim\Postsystem\Blog;

use Maxim\Postsystem\UUID;

class Like
{
    private UUID $uuid;
    private Post $post;
    private User $author;

    public function __construct(UUID $uuid, Post $post, User $author)
    {
        $this->uuid = $uuid;
        $this->post = $post;
        $this->author = $author;
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
     * Get the value of post
     *
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
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
}