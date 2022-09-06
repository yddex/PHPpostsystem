<?php
namespace Maxim\Postsystem\Http\Actions\UserActions;

use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;




class UserFindByLogin implements IAction
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(Request $request): Response
    {
        try{
            //извлекаем логин из запроса
            $login = $request->query("login");
            
            //ищем пользователя в репозитории
            $user = $this->userRepository->getByLogin($login);

        }catch(HttpException | UserNotFoundException $e){
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            "uuid" => (string)$user->getUuid(),
            "name" => $user->getName()->getName(),
            "surname" => $user->getName()->getSurname(),
            "login" => $user->getLogin()
        ]);
    }
}