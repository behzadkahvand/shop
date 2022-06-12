<?php

namespace App\Service\Holiday\Adapters;

use App\Entity\Seller;
use App\Service\Holiday\HolidayServiceInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class CacheHolidayService
 */
final class CacheHolidayService extends AbstractHolidayService
{
    use HolidayCacheKeyTrait;

    /**
     * @var HolidayServiceInterface
     */
    private HolidayServiceInterface $inner;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * CacheHolidayService constructor.
     */
    public function __construct(HolidayServiceInterface $inner, CacheInterface $cache)
    {
        $this->inner = $inner;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function isOpenForShipment(\DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        if (0 === count($sellers)) {
            return $this->cache->get($this->shipmentKey($dateTime), function (ItemInterface $item) use ($dateTime) {
                $result = $this->inner->isOpenForShipment($dateTime);

                $item->expiresAfter(6 * 60 * 60); // 6 Hour

                return $result;
            });
        }

        $result = 0;
        foreach ($sellers as $seller) {
            $result += (int) $this->cache->get(
                $this->shipmentKey($dateTime, $seller),
                function (ItemInterface $item) use ($dateTime, $seller) {
                    $result = $this->inner->isOpenForShipment($dateTime, $seller);

                    $item->expiresAfter(6 * 60 * 60); // 6 Hour

                    return $result;
                }
            );
        }

        return count($sellers) === $result;
    }

    /**
     * @inheritDoc
     */
    public function isOpenForSupply(\DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        if (0 === count($sellers)) {
            return $this->cache->get(
                $this->supplyKey($dateTime),
                function (ItemInterface $item) use ($dateTime, $sellers) {
                    $result = $this->inner->isOpenForSupply($dateTime, ...$sellers);

                    $item->expiresAfter(6 * 60 * 60); // 6 Hour

                    return $result;
                }
            );
        }

        $result = 0;
        foreach ($sellers as $seller) {
            $result += (int) $this->cache->get(
                $this->supplyKey($dateTime, $seller),
                function (ItemInterface $item) use ($dateTime, $seller) {
                    $result = $this->inner->isOpenForSupply($dateTime, $seller);

                    $item->expiresAfter(6 * 60 * 60); // 6 Hour

                    return $result;
                }
            );
        }

        return count($sellers) === $result;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'cache';
    }
}
