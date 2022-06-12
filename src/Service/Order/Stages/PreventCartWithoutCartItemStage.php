<?php

namespace App\Service\Order\Stages;

use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PreventCartWithoutCartItemStage
 */
final class PreventCartWithoutCartItemStage implements TagAwarePipelineStageInterface
{
    private ValidatorInterface $validator;

    /**
     * PreventCartWithoutCartItemStage constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        $cart       = $payload->getCart();
        $violations = $this->validator->validate($cart, null, ['order.store']);

        if (0 < count($violations)) {
            throw new ValidationFailedException($cart, $violations);
        }

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 104;
    }
}
