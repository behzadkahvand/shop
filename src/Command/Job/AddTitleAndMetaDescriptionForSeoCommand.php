<?php

namespace App\Command\Job;

use App\Repository\CategoryRepository;
use App\Service\Seo\AddTitleAndMetaDescriptionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddTitleAndMetaDescriptionForSeoCommand extends Command
{
    protected static $defaultName = 'timcheh:job:add-title-and-meta-description-for-seo';

    public function __construct(
        private CategoryRepository $categoryRepository,
        private AddTitleAndMetaDescriptionService $addTitleAndMetaDescriptionService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add title and meta description if equal to null');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categoryIds = $this->categoryRepository->findAllIds();
        $this->addTitleAndMetaDescriptionService->handle($categoryIds);

        $io->success('You have successfully added title or meta description');
        return Command::SUCCESS;
    }
}
