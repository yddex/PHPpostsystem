<?php
namespace Maxim\Postsystem\Http\Actions\PostsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\IdentificationInterface;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\Warning;
use Psr\Log\LoggerInterface;

class PostCreate implements IAction
{
    private IPostRepository $postRepository;
    private IdentificationInterface $userIdentification;
    private LoggerInterface $logger;

    public function __construct(IPostRepository $postRepository, IdentificationInterface $userIdentification, LoggerInterface $logger)
    {
        $this->postRepository = $postRepository;
        $this->userIdentification = $userIdentification;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        try{
            //идентифицируем пользователя
            $author = $this->userIdentification->user($request);

             //создаем пост
            $post = new Post(
                UUID::random(),
                $author,
                $request->jsonBodyField("title"),
                $request->jsonBodyField("text")
            );

        }catch(HttpException | InvalidArgumentException | AuthException $e){
            $this->logger->warning("Post create action. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем в репозиторий
        $this->postRepository->save($post);

        return new SuccessfulResponse([
            "uuid" => (string)$post->getUuid()
        ]);
    }
}