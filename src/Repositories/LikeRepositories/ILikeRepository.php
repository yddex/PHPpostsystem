<?php
namespace Maxim\Postsystem\Repositories\LikeRepositories;

use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\UUID;

interface ILikeRepository
{
    public function save(Like $like) :void;
    public function getByUUID(UUID $uuid) :Like;
    public function getByPost(Post $post) :array;
    public function delete(UUID $uuid) :void;
}