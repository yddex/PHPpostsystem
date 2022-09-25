<?php

namespace Maxim\Postsystem\Commands\FakeData;

use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;

class PopulateDB extends Command
{
    private \Faker\Generator $faker;
    private IUserRepository $userRepository;
    private IPostRepository $postRepository;
    private ICommentRepository $commentRepository;

    public function __construct(
        \Faker\Generator $faker,
        IUserRepository $userRepository,
        IPostRepository $postRepository,
        ICommentRepository $commentRepository
      )
    {
        parent::__construct();
        $this->faker = $faker;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
    }

    protected function configure()
    {
        $this
            ->setName("fake-data:populate-db")
            ->setDescription("Fill db with fake data")
            ->addOption('users-number', 'u', InputOption::VALUE_OPTIONAL, 'Users count')
            ->addOption('posts-number', 'p', InputOption::VALUE_OPTIONAL, 'Posts count')
            ->addOption('comments-number', 'c', InputOption::VALUE_OPTIONAL, 'Comments count');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $usersCount = (int)$input->getOption('users-number');
        $postCount = (int)$input->getOption('posts-number');
        $commentsCount = (int)$input->getOption('comments-number');

        if(empty($usersCount) || !is_int($usersCount) || $usersCount <= 0){
            $usersCount = 10;
        }
        if(empty($postCount) || !is_int($postCount) || $postCount < 0){
            $postCount = 20;
        }
        if(empty($commentsCount) || !is_int($commentsCount) || $commentsCount < 0){
            $commentsCount = 12;
        }


        $users = [];
        for($i = 0; $i < $usersCount; $i++){
            $user = $this->createFakeUser();
            $output->writeln("Fake user created. login: " . $user->getLogin());
            $users[] = $user;
        }

        $posts = [];

        foreach($users as $user){

            for($i = 0; $i < $postCount; $i++){
                $post = $this->createFakePost($user);
                $output->writeln("Fake post created. " . $post->getUuid());
                $posts[] = $post;
            }

        }


        foreach($posts as $post){

            for($i = 0; $i < $commentsCount; $i++){
                $user = $users[rand(0, $usersCount-1)];
                $comment = $this->createFakeComment($user, $post);
                $output->writeln("Comment created " . $comment->getUuid() . ". Author: " . $user->getLogin());
            }
        }

        return Command::SUCCESS;

    }


    private function createFakeUser(): User
    {
        $user = User::createFrom(
            new Name(
                $this->faker->firstName,
                $this->faker->lastName
            ),
            $this->faker->userName,
            $this->faker->password,

        );

        $this->userRepository->save($user);
        return $user;
    }

    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            $this->faker->sentence(6, true),
            $this->faker->realText . "fakerMark"
        );

        $this->postRepository->save($post);
        return $post;
    }

    private function createFakeComment(User $author, Post $post) :Comment
    {
        $comment = new Comment(
            UUID::random(),
            $author,
            $post,
            $this->faker->text(150)
        );

        $this->commentRepository->save($comment);

        return $comment;

    }
}
