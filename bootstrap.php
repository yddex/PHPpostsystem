<?php

use Dotenv\Dotenv;
use Maxim\Postsystem\Container\DIContainer;
use Maxim\Postsystem\Http\Auth\BearerTokenAuthentication;
use Maxim\Postsystem\Http\Auth\IAuthentication;
use Maxim\Postsystem\Http\Auth\IdentificationInterface;
use Maxim\Postsystem\Http\Auth\Interfaces\IPasswordAuthentication;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\Auth\JsonBodyUuidIdentification;
use Maxim\Postsystem\Http\Auth\PasswordAuthentication;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\IAuthTokenRepository;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\SqliteAuthTokenRepository;
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

//Репозитории
$container->bind(IUserRepository::class, SqliteUserRepository::class);
$container->bind(IAuthTokenRepository::class, SqliteAuthTokenRepository::class);
$container->bind(IPostRepository::class, SqlitePostRepository::class);
$container->bind(ICommentRepository::class, SqliteCommentRepository::class);
$container->bind(ILikeRepository::class, SqliteLikeRepository::class);

//логгер
$container->bind(LoggerInterface::class, $logger);

//аутентификация
$container->bind(IPasswordAuthentication::class, PasswordAuthentication::class);
$container->bind(ITokenAuthentication::class, BearerTokenAuthentication::class);



return $container;