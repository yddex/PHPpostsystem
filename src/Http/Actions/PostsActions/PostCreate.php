<?php
namespace Maxim\Postsystem\Http\Actions\PostsActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;

class PostCreate implements IAction
{
    private IPostRepository $postRepository;
    private IUserRepository $userRepository;

    public function __construct(IPostRepository $postRepository, IUserRepository $userRepository)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(Request $request): Response
    {
        try{
            //извлекаем uuid автора
            $authorUuid = new UUID($request->jsonBodyField("author_uuid"));
            $author = $this->userRepository->getByUUID($authorUuid);

             //создаем пост
            $post = new Post(
                UUID::random(),
                $author,
                $request->jsonBodyField("title"),
                $request->jsonBodyField("text")
            );

        }catch(HttpException | InvalidArgumentException | UserNotFoundException $e){
            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем в репозиторий
        $this->postRepository->save($post);

        return new SuccessfulResponse([
            "uuid" => (string)$post->getUuid()
        ]);
    }
}