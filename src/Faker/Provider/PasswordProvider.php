<?php

namespace App\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class PasswordProvider
 *
 * @package App\Faker\Provider
 */
final class PasswordProvider extends BaseProvider
{
    private UserPasswordHasherInterface $hasher;

    /**
     * PasswordProvider constructor.
     *
     * @param Generator $generator
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(Generator $generator, UserPasswordHasherInterface $hasher)
    {
        parent::__construct($generator);
        $this->hasher = $hasher;
    }

    /**
     * Encode password.
     *
     * @param        $user
     * @param string $plainPassword
     *
     * @return string
     */
    public function encodePassword($user, string $plainPassword)
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }
}
