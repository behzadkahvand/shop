<?php

namespace App\Service\Order\Stages;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Cart;
use App\Exceptions\Order\MinimumOrderException;
use App\Service\Configuration\ConfigurationService;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class CheckMinimumCartStage implements TagAwarePipelineStageInterface
{
    public function __construct(private ConfigurationService $configurationService)
    {
    }

    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        /** @var Cart $cart */
        $cart = $payload->getCart();

        // For testing
        if ($cart->getCustomer()->getMobile() === '09365985401') {
            return $payload;
        }

        if ($cart->getItemsGrandTotal() < $this->getMinimumCart()) {
            throw new MinimumOrderException();
        }

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 101;
    }

    private function getMinimumCart(): ?int
    {
        $configuration = $this->configurationService->findByCode(ConfigurationCodeDictionary::MINIMUM_CART);
        $value = $configuration?->getValue();

        return $value ?: 0;
    }
}
