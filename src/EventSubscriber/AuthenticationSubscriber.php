<?php

namespace App\EventSubscriber;

use App\Entity\Customer;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    private int $expireTime;

    private int $refreshTokenTtl;

    /**
     * AuthenticationSuccessListener constructor.
     *
     * @param int $expireTime
     * @param int $refreshTokenTtl
     */
    public function __construct(int $expireTime, int $refreshTokenTtl)
    {
        $this->expireTime = $expireTime;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    /**
     * Listener
     *
     * @param AuthenticationSuccessEvent $event
     * @throws ExceptionInterface
     */
    public function onAuthenticationSuccessHandler(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $account = [
            'id'   => $user->getId(),
            'name' => $user->getName(),
        ];

        if ($user instanceof Customer) {
            $account = array_merge($account, [
                'family'             => $user->getFamily(),
                'addresses'          => $user->getAddresses(),
                'isProfileCompleted' => $user->isProfileCompleted(),
            ]);
        }

        $event->setData([
            'token'           => $data['token'],
            'refreshToken'    => $data['refresh_token'],
            'refreshTokenTtl' => $this->refreshTokenTtl,
            'tokenType'       => 'Bearer',
            'expireDate'      => $this->expireTime,
            'account'         => $account,
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccessHandler',
        ];
    }
}
