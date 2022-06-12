<?php

namespace App\Validator;

use App\Repository\InventoryUpdateDemandRepository;
use App\Repository\InventoryUpdateSheetRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InventoryUpdateExcelFileValidator extends ConstraintValidator
{
    private InventoryUpdateDemandRepository $inventoryUpdateDemandRepository;

    private InventoryUpdateSheetRepository $inventoryUpdateSheetRepository;

    public function __construct(
        InventoryUpdateDemandRepository $inventoryUpdateDemandRepository,
        InventoryUpdateSheetRepository $inventoryUpdateSheetRepository
    ) {
        $this->inventoryUpdateDemandRepository = $inventoryUpdateDemandRepository;
        $this->inventoryUpdateSheetRepository = $inventoryUpdateSheetRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\InventoryUpdateExcelFile */

        if (null === $value || '' === $value) {
            return;
        }

        $existingSheet = $this
            ->inventoryUpdateSheetRepository
            ->findOneBy(['fileName' => $value->getClientOriginalName()]);

        if ($existingSheet) {
            $this->context->buildViolation('فایل آپلود شده قبلا پردازش شده است.')
                ->addViolation();
            return;
        }

        $demand = $this->inventoryUpdateDemandRepository->findOneBy([
            'fileName' => $value->getClientOriginalName(),
            'seller' => $constraint->seller
        ]);

        if (null === $demand) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->getClientOriginalName())
                ->addViolation();
            return;
        }

        if ($demand->isExpired()) {
            $this->context->buildViolation('فایل آپلود شده منقضی شده است.')
                ->addViolation();
        }
    }
}
