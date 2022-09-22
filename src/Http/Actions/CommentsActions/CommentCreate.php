<?php
namespace Maxim\Postsystem\Http\Actions\CommentsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\UUID;
use Psr\Log\LoggerInterface;

class CommentCreate implements IAction
{
    private ICommentRepository $commentRepository;
    private ITokenAuthentication $userAuthentication;
    private IPostRepository $postRepository;
    private LoggerInterface $logger;

    public function __construct(
        ICommentRepository $commentRepository,
        ITokenAuthentication $userAuthentication,
        IPostRepository $postRepository,
        LoggerInterface $logger
    )
    {
        $this->commentRepository = $commentRepository;
        $this->userAuthentication = $userAuthentication;
        $this->postRepository = $postRepository;
        $this->logger = $logger;
  
    }

    public function handle(Request $request): Response
    {
        try{
            //Аутентификация пользователя по токену
            $author = $this->userAuthentication->user($request);

            //Извлекаем uuid поста из запроса и ищем пост в репозитории
            $postUuid = new UUID($request->jsonBodyField("post_uuid"));
            $post = $this->postRepository->getByUUID($postUuid);

            $text = $request->jsonBodyField("text");

            //Создаем комментарий
            $uuid = UUID::random();
            $comment = new Comment($uuid, $author, $post, $text);

        }catch(HttpException | InvalidArgumentException | AuthException | PostNotFoundException $e){

            $this->logger->warning("COMMENT CREATE ACTION. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем комментарий в репозиторий
        $this->commentRepository->save($comment);
        $this->logger->info("COMMENT CREATED. UUID: " . (string)$comment->getUuid());
        return new SuccessfulResponse([
            "uuid" => (string)$comment->getUuid()
        ]);
    }
}