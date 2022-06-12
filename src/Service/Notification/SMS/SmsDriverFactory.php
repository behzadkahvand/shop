<?php

namespace App\Service\Notification\SMS;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

final class SmsDriverFactory
{
    private ContainerInterface $container;

    private array $availableDrivers;

    public function __construct(ContainerInterface $container, array $availableDrivers)
    {
        $this->container = $container;
        $this->availableDrivers = $availableDrivers;
    }

    public function create(string $driver): SmsDriverInterface
    {
        if ($this->container->has($driver)) {
            return $this->container->get($driver);
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid SMS Driver. Received "%s", expected one of "%s"',
            $driver,
            implode('", "', $this->availableDrivers)
        ));
    }
}
