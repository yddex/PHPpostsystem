<?php
namespace Maxim\Postsystem\Http\Actions\LikeActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeAlreadyExist;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\UUID;

class LikeCreate implements IAction
{
    private ILikeRepository $likeRepository;

    public function __construct(ILikeRepository $likeRepository)
    {
        $this->likeRepository = $likeRepository;
    }

    public function handle(Request $request): Response
    {
        try{   
            $postUuid = new UUID($request->jsonBodyField("post_uuid"));
            $authorUuid = new UUID($request->jsonBodyField("author_uuid"));

            $uuid = UUID::random();
            

            $this->likeRepository->save(new Like($uuid, $postUuid, $authorUuid));

        }catch(HttpException | InvalidArgumentException | LikeAlreadyExist $e){
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            "uuid" => (string)$uuid
        ]);
    }
}