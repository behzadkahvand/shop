<?php

namespace App\Command\Populate;

use App\Dictionary\ShippingCategoryMethods;
use App\Dictionary\ShippingCategoryName;
use App\Dictionary\ShippingCategoryTitle;
use App\Dictionary\ShippingMethodCode;
use App\Dictionary\ShippingMethodName;
use App\Dictionary\ShippingPeriodTime;
use App\Dictionary\Temporary\ShippingMethodPrices;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingMethodPrice;
use App\Entity\ShippingPeriod;
use App\Repository\ZoneRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateShippingEntitiesCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:shipping-entities';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected ZoneRepository $zoneRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Populate shipping methods, categories and periods');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $zones                 = [];
        $zones['TEHRAN']       = $this->zoneRepository->findOneBy(['code' => 'tehran']);
        $zones['OTHER_CITIES'] = $this->zoneRepository->findOneBy(['code' => 'provinces-excluding-tehran']);

        $io = new SymfonyStyle($input, $output);

        $shippingMethods = [];

        $shippingMethodNames = ShippingMethodName::toArray();
        foreach (ShippingMethodCode::toArray() as $key => $item) {
            $shippingMethod = new ShippingMethod();

            $shippingMethod->setCode($item)
                           ->setName($shippingMethodNames[$key]);

            $this->manager->persist($shippingMethod);

            $shippingMethods[$key] = $shippingMethod;
        }

        $shippingCategoryMethods = ShippingCategoryMethods::toArray();
        $shippingCategoryTitles  = ShippingCategoryTitle::toArray();

        foreach (ShippingCategoryName::toArray() as $shippingCategoryName) {
            $shippingCategory = new ShippingCategory();

            $shippingCategory->setName($shippingCategoryName)
                             ->setTitle($shippingCategoryTitles[$shippingCategoryName]);

            foreach ($shippingCategoryMethods[$shippingCategoryName] as $method) {
                $shippingCategory->addMethod($shippingMethods[$method]);
            }

            $this->manager->persist($shippingCategory);
        }

        $shippingMethodPricesPerZone = ShippingMethodPrices::toArray();

        foreach ($shippingMethodPricesPerZone as $zone => $shippingMethodPrices) {
            foreach ($shippingMethodPrices as $methodName => $price) {
                $shippingMethodPrice = new ShippingMethodPrice();
                $shippingMethodPrice->setShippingMethod($shippingMethods[$methodName])
                                    ->setZone($zones[$zone])
                                    ->setPrice($price);

                $this->manager->persist($shippingMethodPrice);
            }
        }

        $shippingPeriodData = [
            [
                'start' => new DateTime(ShippingPeriodTime::FIRST_PERIOD_START),
                'end'   => new DateTime(ShippingPeriodTime::FIRST_PERIOD_END),
            ],
            [
                'start' => new DateTime(ShippingPeriodTime::SECOND_PERIOD_START),
                'end'   => new DateTime(ShippingPeriodTime::SECOND_PERIOD_END),
            ],
        ];

        foreach ($shippingPeriodData as $item) {
            $shippingPeriod = new ShippingPeriod();

            $shippingPeriod->setStart($item['start'])
                           ->setEnd($item['end'])
                           ->setIsActive(true);

            $this->manager->persist($shippingPeriod);
        }

        $this->manager->flush();

        $io->success('You have successfully populated shipping methods, categories and periods!');

        return Command::SUCCESS;
    }
}
