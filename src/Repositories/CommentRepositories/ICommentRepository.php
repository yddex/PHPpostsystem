<?php
namespace Maxim\Postsystem\Repositories\CommentRepositories;

use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\UUID;

interface ICommentRepository
{
    public function save(Comment $comment) :void;
    public function getByUUID(UUID $uuid) :Comment;
    public function getAllByPost(Post $post) :array;
}