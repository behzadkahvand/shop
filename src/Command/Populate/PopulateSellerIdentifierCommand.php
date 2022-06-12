<?php

namespace App\Command\Populate;

use App\Repository\SellerRepository;
use App\Service\Seller\SellerIdentifier\SellerIdentifierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateSellerIdentifierCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:seller-identifier';

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected SellerRepository $sellerRepository,
        protected SellerIdentifierService $sellerIdentifier
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Set seller identifier.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sellers = $this->sellerRepository->findAll();

        foreach ($sellers as $seller) {
            $identifier = $this->sellerIdentifier->generate($seller->getId());

            $seller->setIdentifier($identifier);

            $this->entityManager->persist($seller);
        }

        $this->entityManager->flush();

        $io->success('Seller identifiers set successfully');

        return Command::SUCCESS;
    }
}
