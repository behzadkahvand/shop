<?php

use App\Service\Cart\Exceptions\CartException;
use App\Service\CategoryProductOption\Exceptions\CategoryProductOptionException;
use App\Service\Condition\Exceptions\ConditionException;
use App\Service\CustomerAddress\Exceptions\CustomerAddressException;
use App\Service\ExceptionHandler\ThrowableMetadata;
use App\Service\Order\OrderBalanceRefund\Exceptions\OrderBalanceRefundException;
use App\Service\Order\OrderStatus\Exceptions\OrderStatusException;
use App\Service\Order\UpdateOrderItems\Exceptions\UpdateOrderItemsException;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\UpdatePaymentMethodException;
use App\Service\OrderAffiliator\Exceptions\OrderAffiliatorException;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\OrderShipmentStatusException;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\Exceptions\PartialOrderShipmentTransactionException;
use App\Service\ProductVariant\Exceptions\ProductVariantException;

return [
    PartialOrderShipmentTransactionException::class => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    OrderBalanceRefundException::class              => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    CustomerAddressException::class                 => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    OrderStatusException::class                     => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    UpdateOrderItemsException::class                => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    UpdatePaymentMethodException::class             => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    OrderShipmentStatusException::class             => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    ProductVariantException::class                  => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    CartException::class                            => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    ConditionException::class                       => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    CategoryProductOptionException::class           => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
    OrderAffiliatorException::class                 => function (Throwable $throwable) {
        return new ThrowableMetadata(true, $throwable->getCode(), $throwable->getMessage());
    },
];
