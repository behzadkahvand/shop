<?php

namespace App\Validator\Promotion;

use App\Dictionary\CouponGeneratorInstructionStatus;
use App\Repository\CouponGeneratorInstructionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Validator\Promotion\CouponGeneratorInstruction as CouponGeneratorInstructionConstraint;
use App\Entity\CouponGeneratorInstruction;

class CouponGeneratorInstructionValidator extends ConstraintValidator
{
    private CouponGeneratorInstructionRepository $repository;

    public function __construct(CouponGeneratorInstructionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CouponGeneratorInstructionConstraint $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint CouponGeneratorInstructionConstraint */

        if (!$value instanceof CouponGeneratorInstruction) {
            return;
        }

        $openInstruction = $this->repository->findOneBy([
            'prefix' => $value->getPrefix(),
            'codeLength' => $value->getCodeLength(),
            'suffix' => $value->getSuffix(),
            'status' => [
                CouponGeneratorInstructionStatus::PENDING,
                CouponGeneratorInstructionStatus::STARTED,
            ]
        ]);

        if ($openInstruction) {
            $this->context
                ->buildViolation(
                    "There is a generator instruction with '{{prefix}}' prefix, " .
                    "'{{suffix}}' suffix and '{{codeLength}}' code length in '{{status}}' status"
                )
                ->setParameter('{{prefix}}', $openInstruction->getPrefix())
                ->setParameter('{{suffix}}', $openInstruction->getSuffix())
                ->setParameter('{{codeLength}}', $openInstruction->getCodeLength())
                ->setParameter('{{status}}', $openInstruction->getStatus())
                ->addViolation();
        }
    }
}
