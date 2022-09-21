<?php
namespace Maxim\Postsystem\Http\Actions\CommentsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\IAuthentication;
use Maxim\Postsystem\Http\Auth\IdentificationInterface;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use Psr\Log\LoggerInterface;

class CommentCreate implements IAction
{
    private ICommentRepository $commentRepository;
    private IAuthentication $userAuthentication;
    private IPostRepository $postRepository;
    private LoggerInterface $logger;

    public function __construct(
        ICommentRepository $commentRepository,
        IAuthentication $userAuthentication,
        IPostRepository $postRepository,

    )
    {
        $this->commentRepository = $commentRepository;
        $this->userAuthentication = $userAuthentication;
        $this->postRepository = $postRepository;
  
    }

    public function handle(Request $request): Response
    {
        try{
            //Извлекаем uuid автора из запроса и ищем пользователя в репозитории
            $author = $this->userAuthentication->user($request);

            //Извлекаем uuid поста из запроса и ищем пост в репозитории
            $postUuid = new UUID($request->jsonBodyField("post_uuid"));
            $post = $this->postRepository->getByUUID($postUuid);

            $text = $request->jsonBodyField("text");

            //Создаем комментарий
            $uuid = UUID::random();
            $comment = new Comment($uuid, $author, $post, $text);

        }catch(HttpException | InvalidArgumentException | AuthException | PostNotFoundException $e){

            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем комментарий в репозиторий
        $this->commentRepository->save($comment);

        return new SuccessfulResponse([
            "uuid" => (string)$comment->getUuid()
        ]);
    }
}