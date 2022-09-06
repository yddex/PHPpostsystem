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

class CommentFindByUuid implements IAction
{
    private ICommentRepository $commentRepository;

    public function __construct(ICommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function handle(Request $request): Response
    {
        
        try{
             //извлекаем uuid
            $uuid = new UUID($request->query("uuid"));

             //Ищем комментарий в репозитории
            $comment = $this->commentRepository->getByUUID($uuid);


        }catch(HttpException | InvalidArgumentException | CommentNotFoundException$e){
            return new ErrorResponse($e->getMessage());
        }

       
        return new SuccessfulResponse([
            "uuid" => (string)$comment->getUuid(),
            "author_uuid" => (string)$comment->getAuthor()->getUuid(),
            "post_uuid" => (string)$comment->getPost()->getUuid(),
            "text" => $comment->getText()
        ]);
    }
}