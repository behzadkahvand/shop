<?php

namespace App\Command\Job;

use App\Entity\Holiday;
use App\Repository\OrderShipmentRepository;
use App\Service\Holiday\Adapters\DoctrineHolidayServiceAdapter;
use App\Service\Holiday\Adapters\HolidayCacheKeyTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;

class HolidaysCreateByShipmentCapacityCommand extends Command
{
    use HolidayCacheKeyTrait;

    protected static $defaultName = 'timcheh:holidays:create-by-shipment-capacity';

    public function __construct(
        protected DoctrineHolidayServiceAdapter $holidayService,
        protected OrderShipmentRepository $orderShipmentRepository,
        protected EntityManagerInterface $em,
        protected CacheInterface $cache,
        protected bool $isDebug
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create shipment holiday based on shipment capacity')
            ->addOption('days', null, InputOption::VALUE_OPTIONAL, 'Number of days to check', 14)
            ->addOption('capacity', null, InputOption::VALUE_OPTIONAL, 'Capacity per day', 1100);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io                = new SymfonyStyle($input, $output);
        $i                 = 0;
        $j                 = 0;
        $currentDatetime   = new DateTimeImmutable();
        $days              = (int) abs($input->getOption('days'));
        $capacity          = (int) abs($input->getOption('capacity'));
        $cacheKeysToRefresh = [];

        $io->success(sprintf(
            'Start creating shipment holidays for days with shipment capacity of more than %d up to next %d days',
            $capacity,
            $days
        ));

        while ($i <= $days) {
            $datetime = $currentDatetime->modify("{$j} day");

            $j++;

            if (!$this->isOpenForShipment($datetime)) {
                continue;
            }

            $i++;

            if ($capacity <= $this->getShipmentsCountThatShouldBeDeliveredAt($datetime)) {
                $holiday = (new Holiday())
                    ->setDate($datetime)
                    ->setSupply(false)
                    ->setTitle('Shipment holiday created by system due to reaching daily capacity of ' . $capacity);

                $this->em->persist($holiday);

                $cacheKeysToRefresh[] = $this->shipmentKey($datetime);
            }
        }

        if (0 < count($cacheKeysToRefresh)) {
            $this->em->flush();

            if (!$this->isDebug) {
                $this->refreshCachedHolidays($cacheKeysToRefresh);
            }

            $io->text(sprintf('%d shipment holiday created!', count($cacheKeysToRefresh)));
        } else {
            $io->text('No shipment holiday created!');
        }

        return Command::SUCCESS;
    }

    protected function isOpenForShipment(DateTimeImmutable $datetime): bool
    {
        return $this->holidayService->isOpenForShipment($datetime);
    }

    private function getShipmentsCountThatShouldBeDeliveredAt(DateTimeImmutable $datetime): int
    {
        return $this->orderShipmentRepository->getShipmentsCountThatShouldBeDeliveredAt($datetime);
    }

    protected function refreshCachedHolidays(array $cacheKeysToRefresh): void
    {
        array_walk($cacheKeysToRefresh, function (string $key) {
            $this->cache->delete($key);
            $this->cache->get($key, fn() => false);
        });
    }
}
