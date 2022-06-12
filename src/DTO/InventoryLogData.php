<?php

namespace App\DTO;

class InventoryLogData extends BaseDTO
{
    protected $changeStatus = false;

    protected $loggableProperties = [];

    public function __construct(bool $changeStatus, array $loggableProperties)
    {
        $this->changeStatus       = $changeStatus;
        $this->loggableProperties = $loggableProperties;
    }

    public function isChangeStatus(): bool
    {
        return $this->changeStatus;
    }

    public function getLoggableProperties(): array
    {
        return $this->loggableProperties;
    }

    public function addLoggableProperty(string $propertyName, $propertyValue): self
    {
        if (!isset($this->loggableProperties[$propertyName])) {
            $this->loggableProperties[$propertyName] = $propertyValue;
        }

        return $this;
    }
}
