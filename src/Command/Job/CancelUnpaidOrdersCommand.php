<?php

namespace App\Command\Job;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Entity\Order;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderRepository;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusException;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\SystemChangeOrderShipmentStatus;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CancelUnpaidOrdersCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = 'timcheh:job:cancel-unpaid-orders';

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected OrderRepository $orderRepository,
        protected CreateOrderStatusLogService $createOrderStatusLogService,
        protected SystemChangeOrderShipmentStatus $changeOrderShipmentStatus,
        protected EventDispatcherInterface $eventDispatcher,
        protected ManagerRegistry $registry,
        protected OrderWalletPaymentHandler $orderWalletPaymentHandler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('This job will cancel all unpaid orders after 1 hour.');
    }

    /**
     * @throws InvalidOrderShipmentStatusException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $unpaidOrders = $this->orderRepository->findAllUnpaidOrdersAfterOneHour();

        $io->block('count orders: ' . count($unpaidOrders));

        /** @var Order $unpaidOrder */
        foreach ($unpaidOrders as $unpaidOrder) {
            $io->info('Canceling order with id ' . $unpaidOrder->getId() . ' by system');

            $this->entityManager->beginTransaction();

            $unpaidOrder = $this->refresh($unpaidOrder);

            try {
                $unpaidOrder->releaseReservedStock();// Release reserved stock for each unpaid order
                $unpaidOrder->setStatus(OrderStatus::CANCELED_SYSTEM);// Change order status

                // Log order status
                $this->createOrderStatusLogService->perform(
                    new CreateOrderStatusLogValueObject(
                        $unpaidOrder,
                        OrderStatus::WAITING_FOR_PAY,
                        OrderStatus::CANCELED_SYSTEM
                    ),
                    false
                );
                // Change and log order shipments
                $this->changeOrderShipmentStatus->change($unpaidOrder, OrderShipmentStatus::CANCELED);

                $this->orderWalletPaymentHandler->handle($unpaidOrder, TransferReason::ORDER_CANCELED_BY_SYSTEM);

                $this->entityManager->flush();
                $this->entityManager->commit();

                $this->eventDispatcher->dispatch(new OrderBalanceAmountEvent($unpaidOrder->getId()));
            } catch (Exception $e) {
                $this->entityManager->close();
                $this->entityManager->rollback();
                $this->registry->resetManager();

                $this->logger->error(
                    'Exception in cancelling unpaid order: ',
                    [
                        'message' => $e->getMessage(),
                        'orderId' => $unpaidOrder->getId(),
                        'trace' => $e->getTrace()
                    ]
                );

                $io->text('Failed to cancel order with id: ' . $unpaidOrder->getId());
            }
        }

        return Command::SUCCESS;
    }

    private function refresh(Order $order): Order
    {
        if (!$this->entityManager->contains($order)) {
            $order = $this->orderRepository->find($order->getId());
        }

        return $order;
    }
}
