<?php

namespace App\Service\Product\NotifyMe;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use App\Repository\ProductNotifyRequestRepository;
use App\Service\Product\NotifyMe\Exceptions\NotifyRequestAlreadyExistsException;
use App\Service\Product\NotifyMe\Exceptions\NotifyRequestNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class NotifyMeService
{
    private ProductNotifyRequestRepository $repository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ProductNotifyRequestRepository $repository,
        EntityManagerInterface $entityManager
    ) {
        $this->repository    = $repository;
        $this->entityManager = $entityManager;
    }

    public function makeRequest(ProductNotifyRequest $notifyRequest)
    {
        if ($productNotifyRequest = $this->existsRequest($notifyRequest->getCustomer(), $notifyRequest->getProduct())) {
            throw new NotifyRequestAlreadyExistsException();
        }

        $this->entityManager->persist($notifyRequest);
        $this->entityManager->flush();

        return $notifyRequest;
    }

    public function removeRequest(Customer $customer, Product $product): bool
    {
        if (!$notifyRequest = $this->existsRequest($customer, $product)) {
            throw new NotifyRequestNotFoundException();
        }

        $this->entityManager->remove($notifyRequest);
        $this->entityManager->flush();

        return true;
    }

    public function existsRequest(Customer $customer, Product $product): ?ProductNotifyRequest
    {
        return $this->repository->findCustomerProductNotifyRequestOnProduct($customer, $product);
    }
}
