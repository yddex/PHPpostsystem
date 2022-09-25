<?php

namespace Maxim\Postsystem\Commands\Users;

use InvalidArgumentException;
use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\UserNotFoundException;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{

    private IUserRepository $userRepository;
    public function __construct(IUserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this->setName('users:update')
            ->setDescription("Update name and surname of user")
            ->addArgument('uuid', InputArgument::REQUIRED, "uuid of user")
            ->addOption('firstname', 'f', InputOption::VALUE_OPTIONAL, "name")
            ->addOption('surname', 's', InputOption::VALUE_OPTIONAL, "surname");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Update user command started");

        try {
            $uuid = new UUID($input->getArgument('uuid'));
            $user = $this->userRepository->getByUUID($uuid);
        } catch (InvalidArgumentException | UserNotFoundException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $name = $input->getOption('firstname');
        $surname = $input->getOption('surname');

        if (empty($name) && empty($surname)) {
            $output->writeln('Nothing to update');
            return Command::SUCCESS;
        }
         $name = empty($name) ? $user->getName()->getName() : $name;
         $surname = empty($surname) ? $user->getName()->getSurname() : $surname;

         $updateName = new Name($name, $surname);
        
         $this->userRepository->update(
            new User($uuid, $updateName, $user->getLogin(), $user->getHashPassword())
         );

        $output->writeln("User updated.");
        return Command::SUCCESS;
    }
}
