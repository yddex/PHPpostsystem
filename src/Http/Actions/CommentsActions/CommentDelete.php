<?php
namespace Maxim\Postsystem\Http\Actions\CommentsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\CommentNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\CommentRepositories\ICommentRepository;
use Maxim\Postsystem\UUID;
use Psr\Log\LoggerInterface;

class CommentDelete implements IAction
{
    private ICommentRepository $commentRepository;
    private ITokenAuthentication $userAuthentication;
    private LoggerInterface $logger;

    public function __construct(ICommentRepository $commentRepository, ITokenAuthentication $userAuthentication, LoggerInterface $logger)
    {
        $this->commentRepository = $commentRepository;
        $this->userAuthentication = $userAuthentication;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        try{
            $author = $this->userAuthentication->user($request);
            $commentUuid = new UUID($request->query("uuid"));
            $comment = $this->commentRepository->getByUUID($commentUuid);

        }catch(HttpException | InvalidArgumentException | CommentNotFoundException | AuthException $e){
            $this->logger->warning("COMMENT DELETE ACTION. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        if((string)$author->getUuid() !== (string)$comment->getAuthor()->getUuid()){
            $this->logger->warning("COMMENT DELETE ACTION. User not owner for comment: " . $author->getUuid());
            return new ErrorResponse("User not owner for comment");
        }

        $this->commentRepository->delete($comment);
        $this->logger->info("COMMENT DELETED. POST UUID: " . $comment->getPost()->getUuid());
        return new SuccessfulResponse([]);
    }
}