<?php
namespace Maxim\Postsystem\Commands\Users;

use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserLoginTakenException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{

    private IUserRepository $userRepository;
    public function __construct(IUserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this->setName('users:create')
             ->setDescription("Creates new user")
             ->addArgument('name', InputArgument::REQUIRED, "Name")
             ->addArgument('surname', InputArgument::REQUIRED, "Surname")
             ->addArgument('login', InputArgument::REQUIRED, "Login")
             ->addArgument('password', InputArgument::REQUIRED, "Password");
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        $output->writeln("Create user command started");

        $user = User::createFrom(
            new Name($input->getArgument('name'), $input->getArgument('surname')),
            $input->getArgument('login'),
            $input->getArgument('password')
        );

        try{
            $this->userRepository->save($user);

        }catch(UserLoginTakenException $e){
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln("User created. UUID: " . $user->getUuid());
        return Command::SUCCESS;
    }
}