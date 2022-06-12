<?php

namespace App\Service\Customer;

use App\Entity\Account;
use App\Entity\Customer;
use App\Exceptions\CacheKeyNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CustomerService implements CustomerServiceInterface
{
    private const CARD_NUMBER = 'cardNumber_';

    private CacheItemPoolInterface $cache;

    private EntityManagerInterface $manager;

    public function __construct(CacheItemPoolInterface $cache, EntityManagerInterface $manager)
    {
        $this->cache = $cache;
        $this->manager = $manager;
    }

    /**
     * @param Customer $customer
     * @param string $cardNumber
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function store(Customer $customer, string $cardNumber): array
    {
        $data =  $this->cache->getItem(self::CARD_NUMBER . $cardNumber)->get();
        if (is_null($data)) {
            throw new CacheKeyNotFoundException();
        }
        $this->removeCustomerAccountIfExists($customer);
        $this->storeCardDataInDataBase($data, $customer);
        $this->cache->deleteItem(self::CARD_NUMBER . $cardNumber);

        return $data;
    }

    private function removeCustomerAccountIfExists(Customer $customer): void
    {
        $account = $this->manager->getRepository(Account::class)->findOneBy(['customer' => $customer]);
        if ($account) {
            $this->manager->remove($account);
            $this->manager->flush();
        }
    }

    private function storeCardDataInDataBase(array $cacheItem, Customer $customer): Account
    {
        $account = new Account();
        $account->setCardNumber($cacheItem['cardNumber']);
        $account->setBank($cacheItem['bank']);
        $account->setShebaNumber($cacheItem['shebaNumber']);
        $account->setFirstName($cacheItem['firstName']);
        $account->setLastName($cacheItem['lastName']);
        $customer->setAccount($account);
        $this->manager->persist($customer);
        $this->manager->flush();

        return $account;
    }

    /**
     * @param string $cardNumber
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function saveCardDataInCache(string $cardNumber, array $data): void
    {
        $cacheItem = $this->cache->getItem(self::CARD_NUMBER . $cardNumber);

        $cacheItem->set($data)->expiresAfter(3600);

        $this->cache->save($cacheItem);
    }

    /**
     * @param array<string> $mobiles
     *
     * @return array<Customer>
     */
    public function getCustomersByMobileList(array $mobiles): array
    {
        $customerRepository = $this->manager->getRepository(Customer::class);
        $customers = $customerRepository->getIdsByMobileList($mobiles);

        return array_map(fn($customer) => $this->manager->getReference(Customer::class, $customer['id']), $customers);
    }
}
