<?php
namespace Maxim\Postsystem\Http\Actions\LikeActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeAlreadyExist;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\UUID;




class LikeCreate implements IAction
{
    private ILikeRepository $likeRepository;
    private ITokenAuthentication $userAuthentication;

    public function __construct(ILikeRepository $likeRepository, ITokenAuthentication $userAuthentication)
    {
        $this->likeRepository = $likeRepository;
        $this->userAuthentication = $userAuthentication;
    }

    public function handle(Request $request): Response
    {
        try{   
            //аутентификация по токену
            $author = $this->userAuthentication->user($request);

            $postUuid = new UUID($request->jsonBodyField("post_uuid"));
            $authorUuid = $author->getUuid();

            $uuid = UUID::random();
            

            $this->likeRepository->save(new Like($uuid, $postUuid, $authorUuid));

        }catch(HttpException | InvalidArgumentException | LikeAlreadyExist | AuthException $e){
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            "uuid" => (string)$uuid
        ]);
    }
}