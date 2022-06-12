<?php

namespace App\Service\Order\ReturnRequest\Validator;

use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Exceptions\Order\ReturnRequest\InvalidReturnRequestException;

class ReturnRequestValidator
{
    /**
     * @throws InvalidReturnRequestException
     */
    public function validate(ReturnRequest $request): void
    {
        $this->validateDuplicateItems($request);
        $this->validateStatus($request);
        $this->validateSameOrderPolicy($request);
        $this->validateQuantity($request);
    }

    /**
     * @throws InvalidReturnRequestException
     */
    private function validateDuplicateItems(ReturnRequest $request): void
    {
        $orderItemIds = $request->getItems()->map(
            fn(ReturnRequestItem $item): int => $item->getOrderItem()->getId()
        )->toArray();

        if ($this->hasDuplicateValues($orderItemIds)) {
            throw new InvalidReturnRequestException('Multiple request items for one order item is not allowed');
        }
    }

    /**
     * @throws InvalidReturnRequestException
     */
    private function validateStatus(ReturnRequest $request): void
    {
        $items = $request->getItems();

        foreach ($items as $item) {
            if (!$item->getOrderItem()->isDelivered()) {
                throw new InvalidReturnRequestException('Only delivered items can be returned');
            }
        }
    }

    /**
     * @throws InvalidReturnRequestException
     */
    private function validateSameOrderPolicy(ReturnRequest $request): void
    {
        $order = $request->getOrder();
        $items = $request->getItems();

        foreach ($items as $item) {
            if (!$item->getOrderItem()->isBelongTo($order)) {
                throw new InvalidReturnRequestException('All items should belong to specified order.');
            }
        }
    }

    /**
     * @throws InvalidReturnRequestException
     */
    private function validateQuantity(ReturnRequest $request)
    {
        $items = $request->getItems();

        foreach ($items as $item) {
            $returnRequestQuantity = $item->getQuantity();
            $maximumReturnableItemsCount = $this->maximumReturnableItemsCount($item);

            if ($returnRequestQuantity > $maximumReturnableItemsCount) {
                throw new InvalidReturnRequestException(
                    'Total return quantities is greater than total item quantities for order item with id: ' . $item->getOrderItem()->getId()
                );
            }
        }
    }

    private function maximumReturnableItemsCount(ReturnRequestItem $item): int
    {
        $orderItem = $item->getOrderItem();
        $orderItemQuantity = $orderItem->getQuantity();
        $returnsCount = $orderItem->getReturnItemsCount();

        return $orderItemQuantity - $returnsCount;
    }

    private function hasDuplicateValues(array $orderItemIds): bool
    {
        return count($orderItemIds) !== count(array_unique($orderItemIds));
    }
}
