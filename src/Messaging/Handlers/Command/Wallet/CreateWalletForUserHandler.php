<?php

namespace App\Messaging\Handlers\Command\Wallet;

use App\Entity\Wallet;
use App\Messaging\Messages\Command\Wallet\CreateWalletForUser;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateWalletForUserHandler implements MessageHandlerInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private CustomerRepository $customerRepository)
    {
    }

    public function __invoke(CreateWalletForUser $createWalletForUser): void
    {
        $qb       = $this->customerRepository->getCustomerQueryBuilder();
        $customer = $qb->select('PARTIAL customer.{id}')
                       ->addSelect('PARTIAL wallet.{id}')
                       ->leftJoin('customer.wallet', 'wallet')
                       ->where('customer.id = :cid')
                       ->andWhere('customer.wallet IS NULL')
                       ->setParameter('cid', $createWalletForUser->getUserId())
                       ->getQuery()
                       ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
                       ->getOneOrNullResult();

        if (isset($customer)) {
            $customer->setWallet(new Wallet());

            $this->entityManager->flush();
        }
    }
}
