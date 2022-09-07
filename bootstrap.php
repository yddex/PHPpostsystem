<?php

use Maxim\Postsystem\Container\DIContainer;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\CommentRepositories\SqliteCommentRepository;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\Repositories\LikeRepositories\SqliteLikeRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;

require_once __DIR__ . "/vendor/autoload.php";

$container = new DIContainer();

//Добавляем в контейнер зависимостей объект для подключения к БД
$container->bind(PDO::class,new PDO("sqlite:" . __DIR__ . "/blog.sqlite"));

//класс репозитория пользователей
$container->bind(IUserRepository::class, SqliteUserRepository::class);

//класс репозитория постов
$container->bind(IPostRepository::class, SqlitePostRepository::class);

//класс репозитория комментариев
$container->bind(ICommentRepository::class, SqliteCommentRepository::class);

//класс репозитория лайков
$container->bind(ILikeRepository::class, SqliteLikeRepository::class);

return $container;