<?php

namespace App\Service\PartialShipment;

use App\Service\PartialShipment\Types\AbstractPartialShipment;
use DateTimeInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PartialShipmentValidator
 */
class PartialShipmentValidator
{
    private ValidatorInterface $validator;

    /**
     * PartialShipmentValidator constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param AbstractPartialShipment $partialShipment
     * @param DateTimeInterface $selectedDateTime
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(
        AbstractPartialShipment $partialShipment,
        DateTimeInterface $selectedDateTime
    ): ConstraintViolationListInterface {
        $violations = iterator_to_array(
            $partialShipment->validate($selectedDateTime, $this->validator)
        );

        return new ConstraintViolationList($violations);
    }
}
