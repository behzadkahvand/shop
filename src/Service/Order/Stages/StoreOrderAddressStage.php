<?php

namespace App\Service\Order\Stages;

use App\Entity\OrderAddress;
use App\Service\CustomerAddress\DefaultCustomerAddressService;
use App\Service\CustomerAddress\Exceptions\UnexpectedCustomerAddressException;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class StoreOrderAddressStage implements TagAwarePipelineStageInterface
{
    protected DefaultCustomerAddressService $customerAddressService;

    public function __construct(DefaultCustomerAddressService $customerAddressService)
    {
        $this->customerAddressService = $customerAddressService;
    }

    /**
     * @throws UnexpectedCustomerAddressException
     */
    public function __invoke(AbstractPipelinePayload $payload)
    {
        $customerAddress = $payload->getCustomerAddress();
        $order = $payload->getOrder();

        $orderAddress = (new OrderAddress())
            ->setCustomerAddress($customerAddress)
            ->setCity($customerAddress->getCity())
            ->setCoordinates($customerAddress->getCoordinates())
            ->setDistrict($customerAddress->getDistrict())
            ->setFamily($customerAddress->getFamily())
            ->setName($customerAddress->getName())
            ->setNationalCode($customerAddress->getNationalCode())
            ->setFullAddress($customerAddress->getFullAddress())
            ->setNumber($customerAddress->getNumber())
            ->setPhone($customerAddress->getMobile())
            ->setPostalCode($customerAddress->getPostalCode())
            ->setUnit($customerAddress->getUnit())
            ->setFloor($customerAddress->getFloor())
            ->setOrder($order)
            ->setIsForeigner($customerAddress->getIsForeigner())
            ->setPervasiveCode($customerAddress->getPervasiveCode())
        ;

        $order->addOrderAddress($orderAddress);

        $this->customerAddressService->set($customerAddress->getCustomer(), $customerAddress, false);

        return $payload;
    }

    public static function getPriority(): int
    {
        return 95;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
