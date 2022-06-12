<?php

namespace App\Service\Order;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Cart;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\InvalidOrderPaymentMethodException;
use App\Service\Pipeline\AbstractPipelinePayload;
use Doctrine\ORM\EntityManagerInterface;

class CreateOrderPayload extends AbstractPipelinePayload
{
    private ?string $utmSource = null;

    private ?string $utmToken = null;
    private Order $order;

    public function __construct(
        private EntityManagerInterface $manager,
        private Cart $cart,
        private CustomerAddress $customerAddress,
        private string $paymentMethod,
        private array $selectedShipments,
        array $affiliatorData,
        private bool $isLegal,
        private bool $useWallet,
    ) {
        if (!empty($affiliatorData)) {
            $this->utmSource = $affiliatorData['utmSource'];
            $this->utmToken  = $affiliatorData['utmToken'];
        }
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getCustomerAddress(): CustomerAddress
    {
        return $this->customerAddress;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        if (!OrderPaymentMethod::isValid($paymentMethod)) {
            throw new InvalidOrderPaymentMethodException();
        }

        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getSelectedShipments(): array
    {
        return $this->selectedShipments;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    public function getUtmSource(): ?string
    {
        return $this->utmSource;
    }

    public function getUtmToken(): ?string
    {
        return $this->utmToken;
    }

    public function isLegal(): bool
    {
        return $this->isLegal;
    }

    public function setIsLegal(bool $isLegal): CreateOrderPayload
    {
        $this->isLegal = $isLegal;
        return $this;
    }

    public function useWallet(): bool
    {
        return $this->useWallet;
    }
}
