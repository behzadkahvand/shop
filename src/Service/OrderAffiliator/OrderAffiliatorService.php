<?php

namespace App\Service\OrderAffiliator;

use App\Entity\Order;
use App\Service\OrderAffiliator\Exceptions\OrderAffiliatorNotFoundException;
use Psr\Log\LoggerInterface;

class OrderAffiliatorService
{
    public function __construct(protected OrderAffiliatorFactory $factory, protected LoggerInterface $logger)
    {
    }

    public function purchase(Order $order)
    {
        try {
            $this->logger->debug('affiliator service');
        } catch (\Throwable) {
        }

        $affiliator = $order->getAffiliator();

        if (!$affiliator) {
            throw new OrderAffiliatorNotFoundException();
        }

        $request = $this->factory->getAffiliatorPurchaseRequest($affiliator->getUtmSource());

        $request->send($order);
    }
}
