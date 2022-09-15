<?php

use Dotenv\Dotenv;
use Maxim\Postsystem\Container\DIContainer;
use Maxim\Postsystem\Http\Auth\IdentificationInterface;
use Maxim\Postsystem\Http\Auth\JsonBodyUuidIdentification;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\CommentRepositories\SqliteCommentRepository;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\Repositories\LikeRepositories\SqliteLikeRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

require_once __DIR__ . "/vendor/autoload.php";
Dotenv::createImmutable(__DIR__)->safeLoad();
$container = new DIContainer();
$logger = new Logger("postsystem");

if($_SERVER["LOG_TO_FILES"] === 'yes'){
    $logger
    ->pushHandler(new StreamHandler( __DIR__ . '/logs/postsystem.log'))
    ->pushHandler(new StreamHandler(__DIR__ . '/logs/postsystem.error.log', level: Level::Error, bubble:false));
}
if($_SERVER["LOG_TO_CONSOLE"]){
    $logger->pushHandler(new StreamHandler("php://stdout"));
}

//Добавляем в контейнер зависимостей объект для подключения к БД

$container->bind(PDO::class,new PDO("sqlite:" . __DIR__ . "/" . $_SERVER['SQLITE_DB_PATH']));

//класс репозитория пользователей
$container->bind(IUserRepository::class, SqliteUserRepository::class);

//класс репозитория постов
$container->bind(IPostRepository::class, SqlitePostRepository::class);

//класс репозитория комментариев
$container->bind(ICommentRepository::class, SqliteCommentRepository::class);

//класс репозитория лайков
$container->bind(ILikeRepository::class, SqliteLikeRepository::class);

$container->bind(LoggerInterface::class, $logger);

$container->bind(IdentificationInterface::class, JsonBodyUuidIdentification::class);

return $container;