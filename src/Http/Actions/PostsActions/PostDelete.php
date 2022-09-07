<?php
namespace Maxim\Postsystem\Http\Actions\PostsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\UUID;

class PostDelete implements IAction
{
    private IPostRepository $postRepository;
    private ICommentRepository $commentRepository;

    public function __construct(IPostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(Request $request): Response
    {
        try{
            //Извлекаем uuid поста из запроса и ищем пост в репозитории
            $postUuid = new UUID($request->query("uuid"));
            $post = $this->postRepository->getByUUID($postUuid);


        }catch(HttpException | InvalidArgumentException | PostNotFoundException $e){
            return new ErrorResponse($e->getMessage());
        }

        $this->postRepository->delete($post);

        return new SuccessfulResponse([]);
    }
}