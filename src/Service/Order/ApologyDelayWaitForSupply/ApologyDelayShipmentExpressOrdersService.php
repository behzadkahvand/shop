<?php

namespace App\Service\Order\ApologyDelayWaitForSupply;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderNote;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Entity\ShippingPeriod;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\Customer\Shipping\WaitingForSupplyShipmentSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\PromotionCouponService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ApologyDelayShipmentExpressOrdersService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected OrderRepository $orderRepository,
        protected CustomerRepository $customerRepository,
        protected PromotionCouponService $promotionCouponService,
        protected EntityManagerInterface $entityManager,
        protected Security $security
    ) {
    }

    public function sendNotifyApologyExpressOrdersWaitForSupply(
        Promotion $promotion,
        DateTime $deliveryDate,
        ShippingPeriod $shippingPeriod
    ): void {
        // TODO : query improvement, because of multiple changes, some functions have complexity and need to improve them

        $expressOrdersWithDelay = $this->orderRepository
            ->getExpressOrdersWithDelayInShipmentOnSpecificDay($deliveryDate, $shippingPeriod);

        $orderCustomerIdsWithPromotion = [];
        foreach ($expressOrdersWithDelay as $itemData) {
            if (intval($itemData['grandTotalItems']) < 200000) {
                $this->sendNotifyWithoutVoucher($itemData);
            } else {
                $orderCustomerIdsWithPromotion[$itemData['orderId']] = $itemData['customerId'];
            }
        }

        if ($orderCustomerIdsWithPromotion) {
            $allGeneratedCouponPerOrder = $this->sendNotifyWithVoucher($promotion, $orderCustomerIdsWithPromotion);
            $this->setOrdersNote($promotion, $allGeneratedCouponPerOrder, $orderCustomerIdsWithPromotion);
        }
    }

    protected function sendNotifyWithoutVoucher($itemData): void
    {
        $this->notificationService->send(
            new WaitingForSupplyShipmentSmsNotificationDTO(
                $itemData['name'],
                $itemData['mobile'],
                $itemData['identifier'],
                $itemData['customerFullName'],
                $itemData['customerId'],
            )
        );
    }

    protected function sendNotifyWithVoucher(
        Promotion $promotion,
        array $orderCustomerIdsWithPromotion
    ): array {
        $allCouponsPerOrder = [];
        $customers          = $this->customerRepository->getCustomersByIds(array_values($orderCustomerIdsWithPromotion));
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $coupon = new PromotionCoupon();
            $coupon->setPromotion($promotion);

            $couponDTO = new PromotionCouponDTO();
            $couponDTO->addCustomer($customer);
            $coupon = $this->promotionCouponService->updateFromDTO($coupon, $couponDTO);

            $allCouponsPerOrder[array_search($customer->getId(), $orderCustomerIdsWithPromotion)] = $coupon;
        }

        return $allCouponsPerOrder;
    }

    protected function setOrdersNote(
        Promotion $promotion,
        array $coupons,
        array $orderCustomerIdsWithPromotion
    ): void {
        $orders = $this->orderRepository->getOrdersByIds(array_keys($orderCustomerIdsWithPromotion));
        /** @var Order $order */
        foreach ($orders as $order) {
            $coupon = $coupons[$order->getId()] ?? null;
            if ($coupon) {
                $this->addApologyOrderNote($order, $coupon->getCode(), $promotion->getName());
            }
        }

        $this->entityManager->flush();
    }

    private function addApologyOrderNote(Order $order, string $couponCode, string $promotionName): void
    {
        $noteDescription = "New apology promotion applied to the order ( Code : {$couponCode} ) - {$promotionName}";
        $currentAdmin    = $this->security->getUser();

        $orderNote = new OrderNote();
        $orderNote->setDescription($noteDescription);
        $orderNote->setAdmin($currentAdmin ?? null);

        $order->addOrderNote($orderNote);

        $this->entityManager->persist($orderNote);
    }
}
