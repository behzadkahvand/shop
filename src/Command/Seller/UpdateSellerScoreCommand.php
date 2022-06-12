<?php

namespace App\Command\Seller;

use App\Service\Seller\SellerScore\SellerScoreBatchUpdateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateSellerScoreCommand extends Command
{
    protected static $defaultName = 'timcheh:update-seller-score';

    public function __construct(
        protected SellerScoreBatchUpdateService $sellerScoreBatchUpdateService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path of file to be read');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->sellerScoreBatchUpdateService->execute($input->getArgument('path'));

        $io->success('File successfully parsed');

        return Command::SUCCESS;
    }
}
