<?php

namespace App\Command\Job;

use App\Repository\ProductRepository;
use App\Service\Product\Colors\AddColorsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddColorsToProductsCommand extends Command
{
    protected static $defaultName = 'timcheh:job:add-colors-to-products';

    public function __construct(
        private ProductRepository $productRepository,
        private AddColorsService $addColorsService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add colors to products');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $productIds = $this->productRepository->getAvailableProductIds();
        if (empty($productIds)) {
            $io->success('There is no product');
            return Command::SUCCESS;
        }
        $this->addColorsService->batchAdd($productIds);

        $io->success('You have successfully added colors to products');
        return Command::SUCCESS;
    }
}
