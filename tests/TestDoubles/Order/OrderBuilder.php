<?php

namespace App\Tests\TestDoubles\Order;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Exceptions\Order\InvalidOrderStatusException;

class OrderBuilder
{
    private Customer $customer;
    private OrderDocument $orderDocument;
    private int $subTotal = 200;
    private int $grandTotal = 150;
    private int $discountTotal = 50;
    private bool $promotionLocked = false;
    private bool $isLegal = true;
    private int $balanceAmount = -150;
    private string $status = OrderStatus::WAIT_CUSTOMER;
    private string $paymentMethod = OrderPaymentMethod::OFFLINE;

    /**
     * OrderBuilder constructor.
     */
    public function __construct()
    {
        $this->customer = new Customer();
        $this->orderDocument = new OrderDocument();
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function withCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @param OrderDocument $document
     * @return $this
     */
    public function withDocument(OrderDocument $document): self
    {
        $this->orderDocument = $document;

        return $this;
    }

    /**
     * @return Order
     * @throws InvalidOrderStatusException
     */
    public function build(): Order
    {
        $order = new Order();
        $order->setCustomer($this->customer)
            ->setOrderDocument($this->orderDocument)
            ->setSubtotal($this->subTotal)
            ->setGrandTotal($this->grandTotal)
            ->setPaymentMethod($this->paymentMethod)
            ->setDiscountTotal($this->discountTotal)
            ->setPromotionLocked($this->promotionLocked)
            ->setIsLegal($this->isLegal)
            ->setBalanceAmount($this->balanceAmount)
            ->setStatus($this->status);

        return $order;
    }
}
