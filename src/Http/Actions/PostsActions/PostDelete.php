<?php
namespace Maxim\Postsystem\Http\Actions\PostsActions;

use InvalidArgumentException;
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

class PostDelete implements IAction
{
    private IPostRepository $postRepository;
    private ITokenAuthentication $userAuthentication;
    private LoggerInterface $logger;

    public function __construct(IPostRepository $postRepository, ITokenAuthentication $userAuthentication, LoggerInterface $logger)
    {
        $this->postRepository = $postRepository;
        $this->userAuthentication = $userAuthentication;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        try{
            
            $author = $this->userAuthentication->user($request);
            $postUuid = new UUID($request->query("uuid"));
            $post = $this->postRepository->getByUUID($postUuid);


        }catch(HttpException | InvalidArgumentException | PostNotFoundException | AuthException $e){
            $this->logger->warning("POST DELETE ACTION. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        if((string)$post->getAuthor()->getUuid() !== (string)$author->getUuid()){
            $this->logger->warning("POST DELETE ACTION. User not owner post. current user uuid: " . $author->getUuid());
            return new ErrorResponse("User not owner post for delete");
        }

        $this->postRepository->delete($post->getUuid());
        $this->logger->info("POST DELETED. AUTHOR UUID: " . $post->getAuthor()->getUuid());
        return new SuccessfulResponse([]);
    }
}