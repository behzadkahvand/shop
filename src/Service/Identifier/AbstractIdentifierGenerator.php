<?php

namespace App\Service\Identifier;

use Jenssegers\Optimus\Optimus;

/**
 * Class AbstractIdentifierGenerator
 */
abstract class AbstractIdentifierGenerator
{
    private Optimus $optimus;

    /**
     * AbstractIdentifierGenerator constructor.
     */
    final public function __construct()
    {
        $this->optimus = new Optimus($this->getPrime(), $this->getInverse(), $this->getXor());
    }

    public function encode(object $entity): int
    {
        $this->checkEntityIsValid($entity);

        return $this->optimus->encode($entity->getId());
    }

    abstract protected function getPrime(): int;
    abstract protected function getInverse(): int;
    abstract protected function getXor(): int;
    abstract protected function getSupportedEntityType(): string;

    private function checkEntityIsValid(object $entity): void
    {
        $supportedEntity = $this->getSupportedEntityType();

        if (0 !== strpos(get_class($entity), 'App\\Entity')) {
            $message = sprintf('method %s::getSupportedEntityType() must return a valid entity namespace.', static::class);

            throw new \RuntimeException($message);
        }

        if (!$entity instanceof $supportedEntity) {
            throw new \InvalidArgumentException('Expected instance of %s got %s', $supportedEntity, get_class($entity));
        }
    }
}
