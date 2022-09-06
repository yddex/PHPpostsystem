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
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;
use Maxim\Postsystem\UUID;

class PostFindByUuid implements IAction
{
    private IPostRepository $postRepository;

    public function __construct(IPostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }



    public function handle(Request $request): Response
    {
        
        try{
            //Получаем uuid из запроса
            $uuid = new UUID($request->query("uuid"));

            //Ищем пост в репозитории
            $post = $this->postRepository->getByUUID($uuid);

        }catch(HttpException | InvalidArgumentException | PostNotFoundException $e){
            return new ErrorResponse($e->getMessage());
        }

        
        return new SuccessfulResponse([
            "uuid" => (string)$post->getUuid(),
            "author_uuid" => (string)$post->getAuthor()->getUuid(),
            "title" => $post->getTitle(),
            "text" => $post->getText()
        ]);
    }
}