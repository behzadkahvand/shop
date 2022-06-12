<?php

namespace App\Service\PartialShipment\Types;

use DateTimeInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Interface ValidatablePartialShipmentInterface
 */
interface ValidatablePartialShipmentInterface
{
    /**
     * @param DateTimeInterface $selectedDatetime
     * @param ValidatorInterface $validator
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(
        DateTimeInterface $selectedDatetime,
        ValidatorInterface $validator
    ): ConstraintViolationListInterface;

    /**
     * @return bool
     */
    public function isValid(): bool;
}
