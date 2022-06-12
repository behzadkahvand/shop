<?php

namespace App\DataFixtures;

use App\Dictionary\NotificationCodeDictionary;
use App\Dictionary\NotificationSectionDictionary;
use App\Entity\Notification;

class NotificationFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_BLACK_FRIDAY_CAMPAIGN,
            NotificationSectionDictionary::CAMPAIGN
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_SALAM_40_CAMPAIGN,
            NotificationSectionDictionary::CAMPAIGN
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_PENDING_ORDER,
            NotificationSectionDictionary::ORDER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_ABANDONED_CARD,
            NotificationSectionDictionary::CUSTOMER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_CALL_FAILED,
            NotificationSectionDictionary::ORDER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_ORDER_CANCELED,
            NotificationSectionDictionary::ORDER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_ORDER_REGISTERED,
            NotificationSectionDictionary::ORDER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_SEND_ORDER_SURVEY,
            NotificationSectionDictionary::ORDER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_FAILED_PAYMENT,
            NotificationSectionDictionary::PAYMENT
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_BALANCE_REFUND,
            NotificationSectionDictionary::ORDER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_PRODUCT_AVAILABLE,
            NotificationSectionDictionary::INVENTORY
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_RATE_AND_REVIEW,
            NotificationSectionDictionary::PRODUCT_RATE_AND_REVIEW
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::RETURN_REQUEST_REGISTERED,
            NotificationSectionDictionary::RETURN_REQUEST
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::RETURN_REQUEST_IS_REFUNDED,
            NotificationSectionDictionary::RETURN_REQUEST
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::RETURN_REQUEST_IS_WAITING_REFUND,
            NotificationSectionDictionary::RETURN_REQUEST
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_EXPRESS_SHIPPING,
            NotificationSectionDictionary::ORDER_SHIPMENT
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_POST_SHIPPING,
            NotificationSectionDictionary::ORDER_SHIPMENT
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_WAITING_FOR_SHIPMENT,
            NotificationSectionDictionary::ORDER_SHIPMENT
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_WAITING_FOR_SUPPLY_SHIPMENT,
            NotificationSectionDictionary::ORDER_SHIPMENT
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_WALLET_DEPOSIT,
            NotificationSectionDictionary::WALLET
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::CUSTOMER_WALLET_WITHDRAW,
            NotificationSectionDictionary::WALLET
        ));

        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_UPDATE_INVENTORY,
            NotificationSectionDictionary::INVENTORY
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_DEPOT_INVENTORY,
            NotificationSectionDictionary::INVENTORY
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_ON_DEMAND_INVENTORY_FINISHED,
            NotificationSectionDictionary::INVENTORY
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_DELAYED_ORDER,
            NotificationSectionDictionary::SELLER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_LANDING,
            NotificationSectionDictionary::SELLER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_PANEL_ACCOUNT,
            NotificationSectionDictionary::SELLER
        ));
        $this->manager->persist($this->make(
            NotificationCodeDictionary::SELLER_PENDING_ORDER,
            NotificationSectionDictionary::SELLER
        ));

        $this->manager->flush();
    }

    protected function make(string $code, string $section, string $type = "SMS"): Notification
    {
        $notification = (new Notification())
            ->setSection($section)
            ->setCode($code)
            ->setNotificationType($type)
            ->setTemplate('Your payment failed');

        $this->addReference($code, $notification);

        return $notification;
    }
}
