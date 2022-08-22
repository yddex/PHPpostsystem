<?php
namespace Maxim\Postsystem\MyFaker;

use Faker;
use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Person\User;

class MyFaker
{
    private Faker\Generator $faker;
    public function __construct(Faker\Generator $faker)
    {   
       $this->faker = $faker; 
    }

    public function getUser() :User
    {
        $id = $this->faker->unique()->randomDigitNotNull();
        return new User($id, new Name($this->faker->name()), new \DateTimeImmutable());
    }

    public function getPost() :Post
    {
        $id = $this->faker->unique()->randomDigitNotNull();
        $title = $this->faker->realText(rand(10,30));
        $text = $this->faker->realText(rand(30, 60));
        $author = $this->getUser();
    
        return new Post($id, $author, $title, $text);
    }

    public function getComment() :Comment
    {
        $id = $this->faker->unique()->randomDigitNotNull();
        $author = $this->getUser();
        $post = $this->getPost();
        $text = $this->faker->realText(rand(10, 20));
        return new Comment($id, $author, $post, $text);
    }

}