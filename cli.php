<?php

require_once __DIR__ . "/vendor/autoload.php";

use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\AppException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Person\User;
use Maxim\Postsystem\Repositories\SqliteUserRepository;
use Maxim\Postsystem\UUID;

try {
    $connection = require_once __DIR__ . "/sqllitepdo.php";
    $userRepository = new SqliteUserRepository($connection);

    echo $userRepository->getByLogin("Admin");


} catch (AppException $e) {
    echo $e->getMessage();
} catch (Throwable $e)
{
    echo "ERROR" . PHP_EOL;
    echo $e->getMessage();
}
