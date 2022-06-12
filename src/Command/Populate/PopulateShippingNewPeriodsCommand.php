<?php

namespace App\Command\Populate;

use App\Entity\ShippingPeriod;
use App\Repository\ShippingPeriodRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateShippingNewPeriodsCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:new-shipping-periods';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected ShippingPeriodRepository $shippingPeriodRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Populate new shipping periods');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->shippingPeriodRepository->findAll() as $period) {
            $period->setIsActive(false);
        }

        foreach (['09:00' => '13:00', '14:00' => '18:00', '18:00' => '22:00'] as $start => $end) {
            $newPeriod = new ShippingPeriod();
            $newPeriod->setIsActive(true)
                      ->setStart(new DateTime($start))
                      ->setEnd(new DateTime($end));

            $this->manager->persist($newPeriod);
        }

        $this->manager->flush();

        $io->success('You have successfully populated new shipping periods!');

        return Command::SUCCESS;
    }
}
