<?php

namespace App\Command\Populate;

use App\Entity\ProductOption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateGoldWeightOptionCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:gold-weight-option';

    public function __construct(protected EntityManagerInterface $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create gold weight option');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $option = new ProductOption();
        $option->setCode('gold-weight')->setName('وزن طلا');
        $this->manager->persist($option);
        $this->manager->flush();

        $io->success('You have successfully created gold weight size option.');

        return Command::SUCCESS;
    }
}
