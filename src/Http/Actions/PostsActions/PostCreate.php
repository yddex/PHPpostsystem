<?php
namespace Maxim\Postsystem\Http\Actions\PostsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\UUID;
use Psr\Log\LoggerInterface;

class PostCreate implements IAction
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
            //аутентифицируем по токену пользователя
            $author = $this->userAuthentication->user($request);

             //создаем пост
            $post = new Post(
                UUID::random(),
                $author,
                $request->jsonBodyField("title"),
                $request->jsonBodyField("text")
            );

        }catch(HttpException | InvalidArgumentException | AuthException $e){
            $this->logger->warning("POST CREATE ACTION. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем в репозиторий
        $this->postRepository->save($post);
        $this->logger->info("POST CREATED. UUID: " . $post->getUuid());
        return new SuccessfulResponse([
            "uuid" => (string)$post->getUuid()
        ]);
    }
}