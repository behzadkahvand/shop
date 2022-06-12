<?php

namespace App\Command\Populate;

use App\Entity\ProductOption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateUnderwearSizeOptionCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:underwear-size-option';

    public function __construct(protected EntityManagerInterface $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $option = new ProductOption();
        $option->setCode('underwear-size')->setName('سایز لباس زیر');
        $this->manager->persist($option);
        $this->manager->flush();

        $io->success('You have successfully created underwear size option.');

        return Command::SUCCESS;
    }
}
