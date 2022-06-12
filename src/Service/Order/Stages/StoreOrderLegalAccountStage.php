<?php

namespace App\Service\Order\Stages;

use App\Entity\Customer;
use App\Entity\CustomerLegalAccount;
use App\Entity\Order;
use App\Entity\OrderLegalAccount;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class StoreOrderLegalAccountStage implements TagAwarePipelineStageInterface
{
    public function __invoke(AbstractPipelinePayload $payload)
    {
        /**
         * @var Customer $customer
         */
        $customer = $payload->getCustomerAddress()->getCustomer();

        if ($customer->isProfileLegal()) {
            /**
             * @var Order $order
             */
            $order = $payload->getOrder();
            /**
             * @var CustomerLegalAccount $customerLegalAccount
             */
            $customerLegalAccount = $customer->getLegalAccount();

            $orderLegalAccount = (new OrderLegalAccount())
                ->setOrder($order)
                ->setCustomerLegalAccount($customerLegalAccount)
                ->setOrganizationName($customerLegalAccount->getOrganizationName())
                ->setEconomicCode($customerLegalAccount->getEconomicCode())
                ->setNationalId($customerLegalAccount->getNationalId())
                ->setRegistrationId($customerLegalAccount->getRegistrationId())
                ->setProvince($customerLegalAccount->getProvince())
                ->setCity($customerLegalAccount->getCity())
                ->setPhoneNumber($customerLegalAccount->getPhoneNumber());

            $order->addOrderLegalAccount($orderLegalAccount);
        }

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return 94;
    }
}
