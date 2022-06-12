<?php

namespace App\Command\Populate;

use App\Entity\Province;
use App\Entity\ProvinceZone;
use App\Repository\ProvinceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateZonesCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:zones';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected ProvinceRepository $provinceRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->block('Creating default zones');

        $tehranProvince = $this->provinceRepository->findOneBy(['code' => 'tehran']);
        $otherProvinces = $this->provinceRepository->findAllExcludingTehran();

        $tehranZone = new ProvinceZone();
        $tehranZone->setCode('tehran')->setName('تهران')->addProvince($tehranProvince);
        $otherZone = new ProvinceZone();
        $otherZone->setCode('provinces-excluding-tehran')->setName('بقیه شهرستان ها');
        foreach ($otherProvinces as $index => $province) {
            $otherZone->addProvince($province);
        }
        $this->manager->persist($tehranZone);
        $this->manager->persist($otherZone);
        $this->manager->flush();

        $io->success('You have successfully populated default zones');

        return Command::SUCCESS;
    }
}
