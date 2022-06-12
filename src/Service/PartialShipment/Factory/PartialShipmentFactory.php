<?php

namespace App\Service\PartialShipment\Factory;

use App\Entity\Zone;
use App\Service\PartialShipment\Exceptions\MinimumShipmentItemCountException;
use App\Service\PartialShipment\Factory\Pipeline\Payload\CreatePartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigurePartialShipmentPayload;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\Types\ExpressPartialShipment;
use App\Service\PartialShipment\Types\PartialShipment;
use App\Service\Pipeline\PipelineInterface;
use App\Service\Pipeline\PipelineRepository;
use DateTimeImmutable;

/**
 * Class PartialShipmentFactory
 */
class PartialShipmentFactory
{
    /**
     * @var PipelineRepository
     */
    private PipelineRepository $pipelineRepository;

    /**
     * PartialShipmentFactory constructor.
     *
     * @param PipelineRepository $pipelineRepository
     */
    public function __construct(PipelineRepository $pipelineRepository)
    {
        $this->pipelineRepository = $pipelineRepository;
    }

    /**
     * @param DateTimeImmutable $baseShipmentDateTime
     * @param Zone $zone
     * @param array $shipmentItems
     * @param bool $isExpressDelivery
     *
     * @return AbstractPartialShipment
     */
    public function create(
        DateTimeImmutable $baseShipmentDateTime,
        Zone $zone,
        array $shipmentItems,
        bool $isExpressDelivery
    ): AbstractPartialShipment {
        $shippingCategory = $this->getShippingCategory($shipmentItems);
        $class            = $this->decidePartialShipmentClass($isExpressDelivery);
        $partialShipment  = new $class($shippingCategory, $zone, $shipmentItems);
        $payload          = new CreatePartialShipmentPayload($partialShipment, $zone, $baseShipmentDateTime);

        return $this->createPipeline($isExpressDelivery)
                    ->process($payload)
                    ->getPartialShipment();
    }

    /**
     * @param array $shipmentItems
     *
     * @return mixed
     */
    private function getShippingCategory(array $shipmentItems)
    {
        if (empty($shipmentItems)) {
            throw new MinimumShipmentItemCountException(
                'At least 1 shipment item is needed to create a partial shipment'
            );
        }

        $shippingCategory = current($shipmentItems)->getShippingCategory();

        if (null === $shippingCategory) {
            throw new MinimumShipmentItemCountException(
                'Unable to create partial shipment as none of the shipping items has a shipping category.'
            );
        }

        return $shippingCategory;
    }

    /**
     * @param bool $isExpressDelivery
     *
     * @return string
     */
    private function decidePartialShipmentClass(bool $isExpressDelivery): string
    {
        if ($isExpressDelivery) {
            return ExpressPartialShipment::class;
        }

        return PartialShipment::class;
    }

    /**
     * @param bool $isExpressDelivery
     *
     * @return PipelineInterface
     */
    private function createPipeline(bool $isExpressDelivery): PipelineInterface
    {
        $configurator          = $this->getConfigurationClass($isExpressDelivery);
        $pipeline              = $this->getPipeline(CreatePartialShipmentPayload::class);
        $configurationPipeline = $this->getPipeline($configurator);

        return $pipeline->pipe(fn($payload) => $configurator::fromCreatePartialShipmentPayload($payload))
                        ->pipe($configurationPipeline);
    }

    /**
     * @param bool $isExpressDelivery
     *
     * @return string
     */
    private function getConfigurationClass(bool $isExpressDelivery): string
    {
        if ($isExpressDelivery) {
            return ConfigureExpressPartialShipmentPayload::class;
        }

        return ConfigurePartialShipmentPayload::class;
    }

    /**
     * @param string $payload
     *
     * @return PipelineInterface
     */
    private function getPipeline(string $payload): PipelineInterface
    {
        return $this->pipelineRepository->getByPayload($payload);
    }
}
