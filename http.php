<?php

use Maxim\Postsystem\Exceptions\AppException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentCreate;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentDelete;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentFindByUuid;
use Maxim\Postsystem\Http\Actions\LikeActions\LikeCreate;
use Maxim\Postsystem\Http\Actions\LikeActions\LikeDelete;
use Maxim\Postsystem\Http\Actions\PostsActions\PostCreate;
use Maxim\Postsystem\Http\Actions\PostsActions\PostDelete;
use Maxim\Postsystem\Http\Actions\PostsActions\PostFindByUuid;
use Maxim\Postsystem\Http\Actions\UserActions\UserCreate;
use Maxim\Postsystem\Http\Actions\UserActions\UserFindByLogin;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\SqliteCommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\SqlitePostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\SqliteUserRepository;
use Psr\Log\LoggerInterface;

require_once __DIR__ . "/vendor/autoload.php";
$container = require_once __DIR__ . "/bootstrap.php";
$logger = $container->get(LoggerInterface::class);

$request = new Request($_GET, $_SERVER, file_get_contents("php://input"));
try{

    //Метод запроса
    $method = $request->method();

    //Путь запроса
    $path = $request->path();

}catch(HttpException $e){
    $logger->warning($e->getMessage());
    (new ErrorResponse($e->getMessage()))->send();
    return;
}

$routes = [
    "GET" => [
        "/users/show" => UserFindByLogin::class,
        "/posts/show" => PostFindByUuid::class,
        "/comments/show" => CommentFindByUuid::class,
    ],

    "POST" => [
        "/users/create" => UserCreate::class,
        "/posts/create" => PostCreate::class,
        "/comments/create" => CommentCreate::class,
        "/likes/create" => LikeCreate::class
    ],

    "DELETE" => [
        "/posts/delete" => PostDelete::class,
        "/comments/delete" => CommentDelete::class,
        "/likes/delete" => LikeDelete::class
    ]
];

//Проверка на наличие метода и пути в массиве возможных
if(!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])){
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

$actionClassName = $routes[$method][$path];
$action = $container->get($actionClassName);

try{
    $response = $action->handle($request);

}catch(Throwable $e){
    $logger->error($e->getMessage(), ['exception' => $e]);
    (new ErrorResponse($e->getMessage()))->send();
}

$response->send();