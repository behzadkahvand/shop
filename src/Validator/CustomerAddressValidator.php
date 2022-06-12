<?php

namespace App\Validator;

use App\Entity\Customer;
use App\Entity\CustomerAddress as CustomerAddressEntity;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CustomerAddressValidator
 */
final class CustomerAddressValidator extends ConstraintValidator
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * CustomerAddressValidator constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CustomerAddress) {
            throw new UnexpectedTypeException($constraint, CustomerAddress::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof CustomerAddressEntity) {
            throw new UnexpectedTypeException($value, CustomerAddressEntity::class);
        }

        $customer = $this->getUser();

        if ($value->getCustomer()->getId() === $customer->getId()) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }

    private function getUser()
    {
        $user = $this->security->getUser();

        if (!$user instanceof Customer) {
            throw new \LogicException('This form should be used in customer context!');
        }

        return $user;
    }
}
