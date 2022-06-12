<?php

namespace App\Command\Job;

use App\Repository\ProductRepository;
use App\Service\Product\BuyBox\AddBuyBoxService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddBuyBoxToProductCommand extends Command
{
    protected static $defaultName = 'timcheh:job:add-buy-box-to-product';

    public function __construct(
        protected AddBuyBoxService $addBuyBoxService,
        protected ProductRepository $productRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add buy box to products');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $productIds = $this->productRepository->getProductIdsHasInventory();

        $this->addBuyBoxService->addMany($productIds);

        $io->success('You have successfully added buy boxes to products');

        return Command::SUCCESS;
    }
}
