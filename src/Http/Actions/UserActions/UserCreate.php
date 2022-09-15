<?php
namespace Maxim\Postsystem\Http\Actions\UserActions;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\Http\HttpException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserLoginTakenException;
use Maxim\Postsystem\Http\Actions\IAction;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use Psr\Log\LoggerInterface;

class UserCreate implements IAction
{
    private IUserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(IUserRepository $userRepository, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        try{
            //извлекаем данные из тела запроса
            $name = $request->jsonBodyField("name");
            $surname = $request->jsonBodyField("surname");
            $login =  $request->jsonBodyField("login");
            $uuid = UUID::random();

            //создаем пользователя
            $user = new User($uuid, new Name($name, $surname), $login);

        }catch(HttpException | InvalidArgumentException $e){
            $this->logger->warning("User create action. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        //Сохраняем пользователя в репозиторий
        try{
            $this->userRepository->save($user);

        }catch(UserLoginTakenException $e){
            $this->logger->warning("User create action. " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            "uuid" => (string)$user->getUuid()
        ]);
      
    }
}