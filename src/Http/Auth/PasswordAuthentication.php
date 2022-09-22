<?php
namespace Maxim\Postsystem\Http\Auth;

use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\Http\AuthException;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Http\Auth\Interfaces\IPasswordAuthentication;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;

class PasswordAuthentication implements IPasswordAuthentication
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function user(Request $request): User
    {
        try{
            $login = $request->jsonBodyField("login");
            $password = $request->jsonBodyField("password");
            
            $user = $this->userRepository->getByLogin($login);

        }catch(HttpException | UserNotFoundException $e){
            throw new AuthException($e->getMessage());
        }

        if(!$user->validatePassword($password)){
            throw new AuthException("Wrong password!");
        }

        return $user;

    }
}