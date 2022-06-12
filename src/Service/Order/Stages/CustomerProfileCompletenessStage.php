<?php

namespace App\Service\Order\Stages;

use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomerProfileCompletenessStage
 */
final class CustomerProfileCompletenessStage implements TagAwarePipelineStageInterface
{
    private ValidatorInterface $validator;

    /**
     * CustomerProfileCompletenessStage constructor.
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        $customer   = $payload->getCart()->getCustomer();
        $violations = $this->validator->validate($customer, null, ['order.store']);

        if (0 < count($violations)) {
            $violations = new ConstraintViolationList();

            $violations->add(new ConstraintViolation(
                'Customer profile is not completed',
                null,
                [],
                null,
                'customerProfile',
                null
            ));

            throw new ValidationFailedException($customer, $violations);
        }

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 105;
    }
}
