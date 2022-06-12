<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\InitializeInventoryUpdateDemand;
use App\Repository\InventoryUpdateDemandRepository;
use App\Service\Notification\DTOs\Seller\InventoryUpdateInitializedSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateDemandInitializer;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InitializeInventoryUpdateDemandHandler implements MessageHandlerInterface
{
    public function __construct(
        private InventoryUpdateDemandRepository $demandRepository,
        private InventoryUpdateDemandInitializer $inventoryUpdateDemandInitializer,
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService,
    ) {
    }

    public function __invoke(InitializeInventoryUpdateDemand $message): void
    {
        $demand = $this->demandRepository->find($message->getInventoryUpdateDemandId());

        if (!$demand) {
            throw new InvalidArgumentException();
        }

        $this->inventoryUpdateDemandInitializer->initialize($demand);
        $this->entityManager->flush();

        $this->notificationService->send(new InventoryUpdateInitializedSmsNotificationDTO($demand));
    }
}
