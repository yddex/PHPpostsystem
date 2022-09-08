<?php
namespace Maxim\Postsystem\Http\Actions\LikeActions;

use InvalidArgumentException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\UUID;

class LikeDelete implements IAction
{
    private ILikeRepository $likeRepository;

    public function __construct(ILikeRepository $likeRepository)
    {
        $this->likeRepository = $likeRepository;
    }

    public function handle(Request $request): Response
    {
        try{

            $likeUuid = new UUID($request->query("uuid"));

        }catch(HttpException | InvalidArgumentException $e){
            return new ErrorResponse($e->getMessage());
        }

        $this->likeRepository->delete($likeUuid);

        return new SuccessfulResponse([]);
    }
}