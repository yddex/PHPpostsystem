<?php

namespace Maxim\Postsystem\Commands\Posts;

use Maxim\Postsystem\UUID;
use InvalidArgumentException;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\PostNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Maxim\Postsystem\Repositories\PostRepositories\IPostRepository;

class DeletePost extends Command
{
    private IPostRepository $postRepository;
    public function __construct(IPostRepository $postRepository)
    {
        parent::__construct();
        $this->postRepository = $postRepository;
    }

    protected function configure()
    {
        $this
            ->setName('posts:delete')
            ->setDescription('Deletes a post')
            ->addArgument('uuid', InputArgument::REQUIRED, 'uuid of post')
            ->addOption(
                'check-existence',
                'c',
                InputOption::VALUE_NONE,
                'Check if post actually exists',
            );;
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $question = new ConfirmationQuestion('You want delete post [Y\n] ?', false);

        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        try {

            $uuid = new UUID($input->getArgument('uuid'));

            if($input->getOption('check-existence')){
                $this->postRepository->getByUUID($uuid);
            }

        } catch (InvalidArgumentException | PostNotFoundException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }   



        $this->postRepository->delete($uuid);

        $output->writeln("Post deleted.");
        return Command::SUCCESS;
    }
}
