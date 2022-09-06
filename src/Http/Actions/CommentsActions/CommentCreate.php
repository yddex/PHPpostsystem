<?php
namespace Maxim\Postsystem\Http\Actions\CommentsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Comment;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;

class CommentCreate implements IAction
{
    private ICommentRepository $commentRepository;
    private IUserRepository $userRepository;
    private IPostRepository $postRepository;

    public function __construct(
        ICommentRepository $commentRepository,
        IUserRepository $userRepository,
        IPostRepository $postRepository
    )
    {
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    public function handle(Request $request): Response
    {
        try{
            //Извлекаем uuid автора из запроса и ищем пользователя в репозитории
            $authorUuid = new UUID($request->jsonBodyField("author_uuid"));
            $author = $this->userRepository->getByUUID($authorUuid);

            //Извлекаем uuid поста из запроса и ищем пост в репозитории
            $postUuid = new UUID($request->jsonBodyField("post_uuid"));
            $post = $this->postRepository->getByUUID($postUuid);

            $text = $request->jsonBodyField("text");

            //Создаем комментарий
            $uuid = UUID::random();
            $comment = new Comment($uuid, $author, $post, $text);

        }catch(HttpException | InvalidArgumentException | UserNotFoundException | PostNotFoundException $e){

            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем комментарий в репозиторий
        $this->commentRepository->save($comment);

        return new SuccessfulResponse([
            "uuid" => (string)$comment->getUuid()
        ]);
    }
}