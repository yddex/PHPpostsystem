<?php
namespace Maxim\Postsystem\Http\Actions\LikeActions;

use InvalidArgumentException;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFound;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\Auth\Interfaces\ITokenAuthentication;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\UUID;

class LikeDelete implements IAction
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
            $author = $this->userAuthentication->user($request);
            $likeUuid = new UUID($request->query("uuid"));
            $like = $this->likeRepository->getByUUID($likeUuid);

        }catch(HttpException | InvalidArgumentException | LikeNotFoundException |AuthException $e){
            return new ErrorResponse($e->getMessage());
        }

        if((string)$like->getAuthorUuid() !== (string)$author->getUuid()){
            return new ErrorResponse("User not owner this like for delete this");
        }
        $this->likeRepository->delete($likeUuid);

        return new SuccessfulResponse([]);
    }
}