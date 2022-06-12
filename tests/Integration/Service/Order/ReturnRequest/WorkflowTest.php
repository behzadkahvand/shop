<?php

namespace App\Tests\Integration\Service\Order\ReturnRequest;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestStatus;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use App\Tests\Integration\BaseIntegrationTestCase;
use Generator;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class WorkflowTest extends BaseIntegrationTestCase
{
    private Workflow|null $sut;
    private ReturnRequestItem|null $item;

    protected function setUp(): void
    {
        parent::setUp();

        $request = new ReturnRequest();
        $order = new Order();
        $customer = new Customer();
        $order->setCustomer($customer);
        $request->setOrder($order);
        $this->item = new ReturnRequestItem();
        $this->item->setIsReturnable(true);
        $request->addItem($this->item);
        $registry = $this->client->getContainer()->get(Registry::class);
        $this->sut = $registry->get($this->item);
    }

    public function testShouldApplyConfirmTransitionForApprovedAndReturnableItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::APPROVED);
        $this->item->setIsReturnable(true);

        $this->sut->apply($this->item, ReturnRequestTransition::WAREHOUSE_CONFIRM);

        self::assertEquals(ReturnRequestStatus::RETURNING, $this->item->getStatus());
    }

    public function testShouldBlockConfirmTransitionForApprovedAndNonReturnableItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::APPROVED);
        $this->item->setIsReturnable(false);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::WAREHOUSE_CONFIRM);
    }

    /**
     * @dataProvider confirmStatusProvider
     */
    public function testShouldBlockConfirmTransitionForNonApprovedItems(string $status): void
    {
        $this->item->setStatus($status);
        $this->item->setIsReturnable(true);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::WAREHOUSE_CONFIRM);
    }

    public function testShouldApplyWaitForRefundTransitionForApprovedAndNonReturnableItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::APPROVED);
        $this->item->setIsReturnable(false);

        $this->sut->apply($this->item, ReturnRequestTransition::WAIT_FOR_REFUND);

        self::assertEquals(ReturnRequestStatus::WAITING_REFUND, $this->item->getStatus());
    }

    public function testShouldBlockWaitForRefundTransitionForApprovedAndReturnableItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::APPROVED);
        $this->item->setIsReturnable(true);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::WAIT_FOR_REFUND);
    }

    /**
     * @dataProvider waitForRefundStatusProvider
     */
    public function testShouldBlockWaitForRefundTransitionForNonApprovedItems(string $status): void
    {
        $this->item->setStatus($status);
        $this->item->setIsReturnable(true);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::WAIT_FOR_REFUND);
    }

    public function testShouldApplyReceiveTransitionForReturningItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::RETURNING);

        $this->sut->apply($this->item, ReturnRequestTransition::WAREHOUSE_RECEIVE);

        self::assertEquals(ReturnRequestStatus::RETURNED, $this->item->getStatus());
    }

    /**
     * @dataProvider receiveStatusProvider
     */
    public function testShouldBlockReceiveTransitionForNonReturningItems(string $status): void
    {
        $this->item->setStatus($status);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::WAIT_FOR_REFUND);
    }

    public function testShouldApplyEvaluateTransitionForReturnedItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::RETURNED);

        $this->sut->apply($this->item, ReturnRequestTransition::WAREHOUSE_EVALUATE);

        self::assertEquals(ReturnRequestStatus::WAITING_REFUND, $this->item->getStatus());
    }

    /**
     * @dataProvider evaluateStatusProvider
     */
    public function testShouldBlockEvaluateTransitionForNonReturnedItems(string $status): void
    {
        $this->item->setStatus($status);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::WAREHOUSE_EVALUATE);
    }

    public function testShouldApplyRefundTransitionForWaitingRefundItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::WAITING_REFUND);

        $this->sut->apply($this->item, ReturnRequestTransition::REFUND);

        self::assertEquals(ReturnRequestStatus::REFUNDED, $this->item->getStatus());
    }

    /**
     * @dataProvider refundStatusProvider
     */
    public function testShouldBlockRefundTransitionForNonReturningItems(string $status): void
    {
        $this->item->setStatus($status);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::REFUND);
    }

    public function testShouldApplyCancelTransitionForApprovedItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::APPROVED);

        $this->sut->apply($this->item, ReturnRequestTransition::CANCEL);

        self::assertEquals(ReturnRequestStatus::CANCELED, $this->item->getStatus());
    }

    public function testShouldApplyCancelTransitionForReturningItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::RETURNING);

        $this->sut->apply($this->item, ReturnRequestTransition::CANCEL);

        self::assertEquals(ReturnRequestStatus::CANCELED, $this->item->getStatus());
    }

    /**
     * @dataProvider cancelStatusProvider
     */
    public function testShouldBlockCancelTransitionForUncancellableItems($status): void
    {
        $this->item->setStatus($status);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::CANCEL);
    }

    public function testShouldApplyRejectTransitionForReturnedItems(): void
    {
        $this->item->setStatus(ReturnRequestStatus::RETURNED);

        $this->sut->apply($this->item, ReturnRequestTransition::REJECT);

        self::assertEquals(ReturnRequestStatus::REJECTED, $this->item->getStatus());
    }

    /**
     * @dataProvider rejectStatusProvider
     */
    public function testShouldBlockRejectTransitionForNonReturnedItems($status): void
    {
        $this->item->setStatus($status);

        $this->expectException(NotEnabledTransitionException::class);

        $this->sut->apply($this->item, ReturnRequestTransition::REJECT);
    }

    public function confirmStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(ReturnRequestStatus::APPROVED);
    }

    public function waitForRefundStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(ReturnRequestStatus::APPROVED);
    }

    public function receiveStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(ReturnRequestStatus::RETURNING);
    }

    public function evaluateStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(ReturnRequestStatus::RETURNED);
    }

    public function refundStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(ReturnRequestStatus::WAITING_REFUND);
    }

    public function cancelStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(
            ReturnRequestStatus::RETURNING,
            ReturnRequestStatus::APPROVED
        );
    }

    public function rejectStatusProvider(): Generator
    {
        return $this->getAllStatusesExcept(ReturnRequestStatus::RETURNED);
    }

    private function getAllStatusesExcept(...$exclusions): Generator
    {
        $statuses = $this->getAllReturnRequestStatuses();
        foreach ($exclusions as $status) {
            $key = array_search($status, $statuses);
            unset($statuses[$key]);
        }

        foreach ($statuses as $status) {
            yield [$status];
        }
    }

    private function getAllReturnRequestStatuses(): array
    {
        return [
            ReturnRequestStatus::APPROVED,
            ReturnRequestStatus::RETURNING,
            ReturnRequestStatus::RETURNED,
            ReturnRequestStatus::WAITING_REFUND,
            ReturnRequestStatus::REFUNDED,
        ];
    }
}
