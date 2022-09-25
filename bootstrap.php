<?php

use Dotenv\Dotenv;
use Faker\Provider\en_GB\Internet;
use Faker\Provider\en_GB\Person;
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\Text;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Maxim\Postsystem\Container\DIContainer;
use Maxim\Postsystem\Http\Auth\PasswordAuthentication;
use Maxim\Postsystem\Http\Auth\BearerTokenAuthentication;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\Auth\Interfaces\IPasswordAuthentication;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\LikeRepositories\SqliteLikeRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\IAuthTokenRepository;
use Maxim\Postsystem\Repositories\CommentRepositories\SqliteCommentRepository;
use Maxim\Postsystem\Repositories\AuthTokenRepositories\SqliteAuthTokenRepository;
require_once __DIR__ . "/vendor/autoload.php";



Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();
$logger = new Logger("postsystem");

if($_SERVER["LOG_TO_FILES"] === 'yes'){
    $logger
    ->pushHandler(new StreamHandler( __DIR__ . '/logs/postsystem.log'))
    ->pushHandler(new StreamHandler(__DIR__ . '/logs/postsystem.error.log', level: Level::Error, bubble:false));
}
if($_SERVER["LOG_TO_CONSOLE"] === 'yes'){
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


$faker = new Faker\Generator();

$faker->addProvider(new Person($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Lorem($faker));

$container->bind(\Faker\Generator::class, $faker);

return $container;