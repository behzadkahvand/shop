<?php

namespace App\Command\Populate;

use App\Entity\ProductOption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateProductOptionsCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:product:options';

    public function __construct(protected EntityManagerInterface $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Populate product options in database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->block('Importing product options...');

        $option1 = new ProductOption();
        $option1->setCode('hugo-size')->setName('سایز هوگو');
        $this->manager->persist($option1);

        $this->manager->flush();

        $io->success('You have successfully imported product options.');

        return Command::SUCCESS;
    }
}
