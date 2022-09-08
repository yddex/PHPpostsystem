<?php
namespace Maxim\Postsystem\Blog;

use Maxim\Postsystem\UUID;

class Like
{
    private UUID $uuid;
    private UUID $postUuid;
    private UUID $authorUuid;

    public function __construct(UUID $uuid, UUID $postUuid, UUID $authorUuid)
    {
        $this->uuid = $uuid;
        $this->postUuid = $postUuid;
        $this->authorUuid = $authorUuid;
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
     * Get the value of authorUuid
     *
     * @return UUID
     */
    public function getAuthorUuid(): UUID
    {
        return $this->authorUuid;
    }

    /**
     * Get the value of postUuid
     *
     * @return UUID
     */
    public function getPostUuid(): UUID
    {
        return $this->postUuid;
    }
}