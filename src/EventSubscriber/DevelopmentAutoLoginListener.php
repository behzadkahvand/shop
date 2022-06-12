<?php

namespace App\EventSubscriber;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Seller;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class DevelopmentAutoLoginListener
 */
final class DevelopmentAutoLoginListener
{
    private WebsiteAreaService $areaService;

    private EntityManagerInterface $em;

    private JWTTokenManagerInterface $JWTTokenManager;

    private bool $developmentAutoLogin;

    public function __construct(
        WebsiteAreaService $areaService,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $JWTTokenManager,
        bool $developmentAutoLogin = false
    ) {
        $this->areaService          = $areaService;
        $this->em                   = $em;
        $this->JWTTokenManager      = $JWTTokenManager;
        $this->developmentAutoLogin = $developmentAutoLogin;
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->developmentAutoLogin || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->headers->has('Authorization')) {
            return;
        }

        switch ($this->areaService->getArea()) {
            case WebsiteAreaDictionary::AREA_ADMIN:
                $entity = Admin::class;
                break;
            case WebsiteAreaDictionary::AREA_SELLER:
                $entity = Seller::class;
                break;
            default:
                $entity = Customer::class;
                break;
        }

        $user  = $this->em->getRepository($entity)->findOneBy([]);
        $token = $this->JWTTokenManager->create($user);

        $request->headers->set('Authorization', "Bearer {$token}");
    }
}
