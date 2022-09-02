<?php

require_once __DIR__ . "/vendor/autoload.php";

use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Commands\Arguments;
use Maxim\Postsystem\Commands\CreateUserCommand;
use Maxim\Postsystem\Exceptions\AppException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\Repositories\CommentRepositories\SqliteCommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Maxim\Postsystem\UUID;


$connection = require_once __DIR__ . "/sqllitepdo.php";
try {

    // $userRepository = new SqliteUserRepository($connection);
    // $postRepository = new SqlitePostRepository($connection);
    // $commentRepository = new SqliteCommentRepository($connection, $userRepository, $postRepository);


//    $createUserCommand = new CreateUserCommand($userRepository);
//    $createUserCommand->handle(Arguments::fromArgv($argv));


} catch (AppException $e) {
    echo $e->getMessage();
} catch (Throwable $e)
{
    echo "ERROR" . PHP_EOL;
    echo $e->getMessage();
}
