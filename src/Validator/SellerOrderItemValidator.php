<?php

namespace App\Validator;

use App\Entity\Seller;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use App\Entity\SellerOrderItem as SellerOrderItemEntity;

/**
 * Class SellerOrderItemValidator
 */
final class SellerOrderItemValidator extends ConstraintValidator
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * SellerOrderItemValidator constructor.
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
        if (!$constraint instanceof SellerOrderItem) {
            throw new UnexpectedTypeException($constraint, CustomerAddress::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof SellerOrderItemEntity) {
            throw new UnexpectedTypeException($value, SellerOrderItemEntity::class);
        }

        $seller = $this->getUser();

        if ($value->getSeller()->getId() === $seller->getId()) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }

    private function getUser()
    {
        $user = $this->security->getUser();

        if (!$user instanceof Seller) {
            throw new \LogicException('This form should be used in seller context!');
        }

        return $user;
    }
}
