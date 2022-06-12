<?php

namespace App\Service\Seller\SellerOrderItemStatusLog;

use App\Service\Seller\SellerOrderItemStatusLog\ValueObjects\CreateSellerOrderItemStatusLogValueObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CreateSellerOrderItemStatusLogService
{
    protected EntityManagerInterface $manager;

    protected SellerOrderItemStatusLogFactory $factory;

    private Security $security;

    public function __construct(
        EntityManagerInterface $manager,
        SellerOrderItemStatusLogFactory $factory,
        Security $security
    ) {
        $this->manager  = $manager;
        $this->factory  = $factory;
        $this->security = $security;
    }

    public function perform(CreateSellerOrderItemStatusLogValueObject $valueObject, bool $performFlush = true): void
    {
        $user                     = $this->security->getUser();
        $sellerOrderItemStatusLog = $this->factory->getSellerOrderItemStatusLog($user);

        $sellerOrderItemStatusLog->setSellerOrderItem($valueObject->getSellerOrderItem())
                                 ->setStatusFrom($valueObject->getStatusFrom())
                                 ->setStatusTo($valueObject->getStatusTo());

        $this->manager->persist($sellerOrderItemStatusLog);

        if ($performFlush) {
            $this->manager->flush();
        }
    }
}
