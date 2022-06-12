<?php

namespace App\Command\Populate;

use App\Dictionary\ShippingCategoryDeliveries;
use App\Dictionary\ShippingCategoryMethods;
use App\Dictionary\ShippingCategoryTitle;
use App\Dictionary\ShippingMethodCode;
use App\Dictionary\ShippingMethodName;
use App\Dictionary\Temporary\ShippingMethodPrices;
use App\Entity\Delivery;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingMethodPrice;
use App\Repository\ShippingMethodPriceRepository;
use App\Repository\ShippingMethodRepository;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddNewShippingCategoryCommand extends Command
{
    protected static $defaultName = 'timcheh:add:shipping-category';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected ShippingMethodRepository $shippingMethodRepository,
        protected ZoneRepository $zoneRepository,
        protected ShippingMethodPriceRepository $shippingMethodPriceRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add new shipping category to DB')
             ->addOption(
                 "shippingCategoryName",
                 null,
                 InputOption::VALUE_REQUIRED,
                 "name of shipping category that we want to add it to DB",
                 "FMCG"
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io                   = new SymfonyStyle($input, $output);
        $shippingCategoryName = $input->getOption("shippingCategoryName");

        $shippingMethods     = [];
        $shippingMethodNames = ShippingMethodName::toArray();
        foreach (ShippingMethodCode::toArray() as $key => $item) {
            $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $item]);
            if (!$shippingMethod) {
                $shippingMethod = new ShippingMethod();
                $shippingMethod->setCode($item)
                               ->setName($shippingMethodNames[$key]);
                $this->manager->persist($shippingMethod);
            }
            $shippingMethods[$key] = $shippingMethod;
        }
        $this->manager->flush();

        $zones                 = [];
        $zones['TEHRAN']       = $this->zoneRepository->findOneBy(['code' => 'express']);
        $zones['OTHER_CITIES'] = $this->zoneRepository->findOneBy(['code' => 'none-express']);

        $shippingMethodPricesPerZone = ShippingMethodPrices::toArray();
        foreach ($shippingMethodPricesPerZone as $zone => $shippingMethodPrices) {
            foreach ($shippingMethodPrices as $methodName => $price) {
                $shippingMethodPrice = $this->shippingMethodPriceRepository->findOneBy([
                    'shippingMethod' => $shippingMethods[$methodName],
                    'zone'           => $zones[$zone],
                ]);
                if (!$shippingMethodPrice) {
                    $shippingMethodPrice = new ShippingMethodPrice();
                    $shippingMethodPrice->setShippingMethod($shippingMethods[$methodName])
                                        ->setZone($zones[$zone])
                                        ->setPrice($price);
                    $this->manager->persist($shippingMethodPrice);
                }
            }
        }

        $shippingCategoryTitle   = ShippingCategoryTitle::toArray()[$shippingCategoryName];
        $shippingCategoryMethods = ShippingCategoryMethods::toArray()[$shippingCategoryName];

        $shippingCategory = new ShippingCategory();
        $shippingCategory->setName($shippingCategoryName)
                         ->setTitle($shippingCategoryTitle);

        foreach ($shippingCategoryMethods as $method) {
            $shippingCategory->addMethod($shippingMethods[$method]);
        }

        $deliveryRange = ShippingCategoryDeliveries::toArray()[$shippingCategoryName];

        $deliveryObject = new Delivery();
        $deliveryObject->setStart($deliveryRange['start'])
                       ->setEnd($deliveryRange['end']);
        $shippingCategory->setDelivery($deliveryObject);

        $this->manager->persist($shippingCategory);
        $this->manager->persist($deliveryObject);

        $this->manager->flush();

        $io->success('You have successfully added new shipping category');

        return Command::SUCCESS;
    }
}
