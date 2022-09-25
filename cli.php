<?php

require_once __DIR__ . "/vendor/autoload.php";

use Maxim\Postsystem\Commands\FakeData\PopulateDB;
use Maxim\Postsystem\Commands\Users\CreateUser;
use Maxim\Postsystem\Commands\Posts\DeletePost;
use Maxim\Postsystem\Commands\Users\UpdateUser;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;


$container = require_once __DIR__ . "/bootstrap.php";

$application = new Application();

$commandLoader = new ContainerCommandLoader($container, 
    [
        'users:create' => CreateUser::class,
        'users:update' => UpdateUser::class,
        'posts:delete' => DeletePost::class,
        'fake-data:populate-db' => PopulateDB::class
    ]
);


$application->setCommandLoader($commandLoader);
$application->run();

