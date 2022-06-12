<?php

namespace App\Service\Customer;

use App\Entity\Customer;

interface CustomerServiceInterface
{
    public function store(Customer $customer, string $cardNumber): array;

    public function saveCardDataInCache(string $cardNumber, array $data): void;
}
