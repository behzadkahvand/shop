<?php

namespace App\Command\Job;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\ShippingPeriod;
use App\Repository\PromotionRepository;
use App\Repository\ShippingPeriodRepository;
use App\Service\Configuration\ConfigurationService;
use App\Service\Order\ApologyDelayWaitForSupply\ApologyDelayShipmentExpressOrdersService;
use DateTime;
use DateTimeZone;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExpressOrdersDelayShipmentSmsCommand extends Command
{
    protected static $defaultName = 'timcheh:job:sms:delay-express-orders';

    public function __construct(
        protected ApologyDelayShipmentExpressOrdersService $delayShipmentExpressOrdersService,
        protected ShippingPeriodRepository $shippingPeriodRepository,
        protected CacheItemPoolInterface $cache,
        protected LoggerInterface $logger,
        protected PromotionRepository $promotionRepository,
        protected ConfigurationService $configurationService
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription(
            'This job will send a sms notification to users who their express orders have delay to supply'
        );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $currentDateTime = new DateTime('now', new DateTimeZone("Asia/Tehran"));

            $shippingPeriod = $this->findProperShippingPeriod($currentDateTime);

            $cacheKey = $this->getCacheSignature($shippingPeriod->getId());
            if ($this->cache->hasItem($cacheKey)) {
                throw new Exception("You have already run this job today for this shipping period!");
            }

            $promotion = $this->getApologyPromotion();

            $this->delayShipmentExpressOrdersService->sendNotifyApologyExpressOrdersWaitForSupply(
                $promotion,
                $currentDateTime,
                $shippingPeriod
            );

            $this->addToCache($cacheKey);

            $io->success('You have successfully sent sms notification for delay express orders');

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $this->logger->error(
                "There is an error to run ExpressOrderDelayShipmentSms cronjob : " .
                $exception->getMessage(),
                ['exception' => $exception]
            );

            return Command::FAILURE;
        }
    }

    private function getApologyPromotion(): ?\App\Entity\Promotion
    {
        if (null === $promotionId = $this->getConfig(ConfigurationCodeDictionary::WAITING_FOR_SUPPLY_ORDER_APOLOGY_PROMOTION_ID)) {
            throw new \Exception("There is no promotion for apologize waiting for supply orders in configuration table !!");
        }

        if (null === $promotion = $this->promotionRepository->find($promotionId)) {
            throw new \Exception("Promotion with ID $promotionId not found !!");
        }

        return $promotion;
    }

    private function getCacheSignature(int $periodId): string
    {
        return 'apology_express_orders_delay_' . date('Y_m_d') . '_' . $periodId;
    }

    private function findProperShippingPeriod(DateTime $currentDateTime): ShippingPeriod
    {
        /** @var ShippingPeriod $shippingPeriod */
        $shippingPeriod = $this->shippingPeriodRepository->getProperPeriodGivenTime(
            new DateTime($currentDateTime->format('H:i:s'))
        );
        if (!$shippingPeriod) {
            throw new Exception("There is no sippingPeriod for current time E.g. : {$currentDateTime->format('H:i:s')}");
        }

        return $shippingPeriod;
    }

    private function getConfig(string $code): ?int
    {
        $config = $this->configurationService->findByCode($code);

        return $config ? (int) $config->getValue() : null;
    }

    private function addToCache(string $cacheKey): void
    {
        $secondsUntilEndOfDay = strtotime("tomorrow") - time();
        $cacheItem            = $this->cache->getItem($cacheKey);

        $cacheItem
            ->set(['total' => 1])
            ->expiresAfter($secondsUntilEndOfDay);

        $this->cache->save($cacheItem);
    }
}
