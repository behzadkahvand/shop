<?php

namespace App\Service\Notification;

use App\Entity\Customer;
use App\Entity\Seller;
use App\Messaging\Messages\Command\Notification\Recipient;
use Symfony\Component\Security\Core\User\UserInterface;

class RecipientFactory
{
    public function make(UserInterface $user): Recipient
    {
        assert($user instanceof Customer || $user instanceof Seller);

        return new Recipient(
            $user->getMobile(),
            $user->getFullName(),
            get_class($user),
            $user->getId()
        );
    }
}
