<?php

namespace App\Service\Configuration;

use App\Entity\Configuration;

/**
 * Interface ConfigurationServiceInterface
 */
interface ConfigurationServiceInterface
{
    /**
     * @param string $code
     *
     * @return Configuration|null
     */
    public function findByCode(string $code): ?Configuration;

    /**
     * @param string ...$codes
     *
     * @return array<string,Configuration|null>
     */
    public function findByCodes(string ...$codes): array;
}
