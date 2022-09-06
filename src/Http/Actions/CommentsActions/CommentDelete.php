<?php
namespace Maxim\Postsystem\Http\Actions\CommentsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\CommentNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\UUID;

class CommentDelete implements IAction
{
    private ICommentRepository $commentRepository;

    public function __construct(ICommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function handle(Request $request): Response
    {
        try{
            //извлекаем uuid поста из запроса и ищем его в репозитории
            $commentUuid = new UUID($request->query("uuid"));
            $comment = $this->commentRepository->getByUUID($commentUuid);

        }catch(HttpException | InvalidArgumentException | CommentNotFoundException $e){
            return new ErrorResponse($e->getMessage());
        }

        $this->commentRepository->delete($comment);

        return new SuccessfulResponse([]);
    }
}