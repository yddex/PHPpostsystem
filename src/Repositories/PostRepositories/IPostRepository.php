<?php
namespace Maxim\Postsystem\Repositories\PostRepositories;

use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\UUID;

interface IPostRepository
{
    public function save(Post $post) :void;
    public function getAll() :array;
    public function getByUUID(UUID $uuid) :Post;
    public function getAllByAuthor(User $author) :array;
}