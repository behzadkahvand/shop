<?php

namespace App\Service\Holiday;

use App\Service\Holiday\Adapters\FridayHolidayServiceAdapter;
use Psr\Container\ContainerInterface;

/**
 * Class HolidayServiceFactory
 */
final class HolidayServiceFactory
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    private array $availableDrivers;

    /**
     * HolidayServiceFactory constructor.
     *
     * @param ContainerInterface $container
     * @param array $availableDrivers
     */
    public function __construct(ContainerInterface $container, array $availableDrivers)
    {
        $this->container = $container;
        $this->availableDrivers = $availableDrivers;
    }

    public function create(string $driver): HolidayServiceInterface
    {
        if ($this->container->has($driver)) {
            return new FridayHolidayServiceAdapter($this->container->get($driver));
        }

        $message = 'Invalid holiday service driver.';

        if (count($this->availableDrivers)) {
            $message .= sprintf(
                ' Received "%s", expected one of "%s".',
                $driver,
                implode('", "', $this->availableDrivers)
            );
        }

        throw new \InvalidArgumentException($message);
    }
}
