<?php

namespace App\Command\Populate;

use App\Dictionary\ShippingCategoryDeliveries;
use App\Entity\Delivery;
use App\Repository\DeliveryRepository;
use App\Repository\ShippingCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateCategoryDeliveriesCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:category-deliveries';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected DeliveryRepository $deliveryRepository,
        protected ShippingCategoryRepository $shippingCategoryRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Removes old category deliveries and populate category deliveries and finally assign them to shipping categories');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->deliveryRepository->createQueryBuilder('deliveryRange')
                                 ->delete()
                                 ->getQuery()
                                 ->execute();

        $deliveries = ShippingCategoryDeliveries::toArray();

        foreach ($deliveries as $shippingCategoryName => $deliveryRange) {
            $shippingCategoryObject = $this->shippingCategoryRepository->findOneBy(['name' => $shippingCategoryName]);

            $deliveryObject = new Delivery();

            $deliveryObject->setStart($deliveryRange['start'])
                           ->setEnd($deliveryRange['end']);

            $shippingCategoryObject->setDelivery($deliveryObject);

            $this->manager->persist($deliveryObject);
        }

        $this->manager->flush();

        $io->success('You have successfully populated category deliveries and assigned them to shipping category!');

        return Command::SUCCESS;
    }
}
