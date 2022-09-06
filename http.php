<?php

use Maxim\Postsystem\Exceptions\AppException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentCreate;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentDelete;
use Maxim\Postsystem\Http\Actions\CommentsActions\CommentFindByUuid;
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



require_once __DIR__ . "/vendor/autoload.php";

$pdo = require_once './sqllitepdo.php';
//репозитории
$userRepository = new SqliteUserRepository($pdo);
$postRepository = new SqlitePostRepository($pdo, $userRepository);
$commentRepository = new SqliteCommentRepository($pdo, $userRepository, $postRepository);

$request = new Request($_GET, $_SERVER, file_get_contents("php://input"));
try{

    $method = $request->method();
    $path = $request->path();
}catch(HttpException $e){
    (new ErrorResponse($e->getMessage()))->send();
    return;
}

$routes = [
    "GET" => [
        "/users/show" => new UserFindByLogin($userRepository),
        "/posts/show" => new PostFindByUuid($postRepository),
        "/comments/show" => new CommentFindByUuid($commentRepository),
    ],

    "POST" => [
        "/users/create" => new UserCreate($userRepository),
        "/posts/create" => new PostCreate($postRepository, $userRepository),
        "/comments/create" => new CommentCreate($commentRepository, $userRepository, $postRepository)
    ],

    "DELETE" => [
        "/posts/delete" => new PostDelete($postRepository, $commentRepository),
        "/comments/delete" => new CommentDelete($commentRepository)
    ]
];


if(!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])){
    (new ErrorResponse("Not found"))->send();
    return;
}

$action = $routes[$method][$path];

try{
    $response = $action->handle($request);

}catch(AppException $e){
    (new ErrorResponse($e->getMessage()))->send();
}

$response->send();