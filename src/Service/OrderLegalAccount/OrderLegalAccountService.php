<?php

namespace App\Service\OrderLegalAccount;

use App\Dictionary\OrderStatus;
use App\DTO\Admin\OrderLegalAccountData;
use App\Entity\Order;
use App\Service\OrderLegalAccount\Exceptions\StoreOrderLegalAccountException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class OrderLegalAccountService
{
    protected EntityManagerInterface $manager;

    protected OrderLegalAccountFactory $factory;

    public function __construct(
        EntityManagerInterface $manager,
        OrderLegalAccountFactory $factory
    ) {
        $this->manager = $manager;
        $this->factory = $factory;
    }

    public function store(Order $order, OrderLegalAccountData $orderLegalAccountData): Order
    {
        $oldLegalAccount = $order->getLegalAccount();

        if (!$oldLegalAccount || $order->getStatus() === OrderStatus::DELIVERED) {
            throw new StoreOrderLegalAccountException();
        }

        $this->manager->beginTransaction();

        try {
            $oldLegalAccount->setIsActive(false);

            $newLegalAccount = $this->factory->getOrderLegalAccount();

            $newLegalAccount
                ->setOrder($order)
                ->setCustomerLegalAccount($oldLegalAccount->getCustomerLegalAccount())
                ->setProvince($orderLegalAccountData->getProvince())
                ->setCity($orderLegalAccountData->getCity())
                ->setOrganizationName($orderLegalAccountData->getOrganizationName())
                ->setEconomicCode($orderLegalAccountData->getEconomicCode())
                ->setNationalId($orderLegalAccountData->getNationalId())
                ->setRegistrationId($orderLegalAccountData->getRegistrationId())
                ->setPhoneNumber($orderLegalAccountData->getPhoneNumber());

            $order->addOrderLegalAccount($newLegalAccount);

            $this->manager->persist($newLegalAccount);
            $this->manager->flush();
            $this->manager->commit();
        } catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollBack();

            throw $e;
        }

        return $order;
    }
}
