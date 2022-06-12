<?php

namespace App\Events\Auth;

use Symfony\Component\Security\Core\User\UserInterface;

class UserDeactivated
{
    public function __construct(protected UserInterface $user)
    {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
