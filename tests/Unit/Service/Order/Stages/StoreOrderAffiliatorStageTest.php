<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Cart;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\OrderAffiliator;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\StoreOrderAffiliatorStage;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class StoreOrderAffiliatorStageTest extends MockeryTestCase
{
    protected ?StoreOrderAffiliatorStage $storeOrderAffiliatorStage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeOrderAffiliatorStage = new StoreOrderAffiliatorStage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->storeOrderAffiliatorStage = null;
    }

    public function testItCanNotStoreOrderAffiliator(): void
    {
        $storeOrderPayload = new CreateOrderPayload(
            Mockery::mock(EntityManagerInterface::class),
            Mockery::mock(Cart::class),
            Mockery::mock(CustomerAddress::class),
            OrderPaymentMethod::OFFLINE,
            [],
            [],
            false,
            false
        );

        $result = $this->storeOrderAffiliatorStage->__invoke($storeOrderPayload);

        self::assertEquals($storeOrderPayload, $result);
    }

    public function testItCanStoreOrderAffiliator(): void
    {
        $storeOrderPayload = new CreateOrderPayload(
            Mockery::mock(EntityManagerInterface::class),
            Mockery::mock(Cart::class),
            Mockery::mock(CustomerAddress::class),
            OrderPaymentMethod::OFFLINE,
            [],
            [
                'utmSource' => 'Takhfifan',
                'utmToken'  => 'tatoken'
            ],
            false,
            false
        );

        $order = new Order();
        $storeOrderPayload->setOrder($order);
        $result = $this->storeOrderAffiliatorStage->__invoke($storeOrderPayload);

        /**
         * @var OrderAffiliator $orderAffiliator
         */
        $orderAffiliator = $result->getOrder()->getAffiliator();

        self::assertEquals($order, $orderAffiliator->getOrder());
        self::assertEquals('Takhfifan', $orderAffiliator->getUtmSource());
        self::assertEquals('tatoken', $orderAffiliator->getUtmToken());
        self::assertEquals($storeOrderPayload, $result);
    }

    public function testGerPriorityAndTag(): void
    {
        self::assertEquals(80, $this->storeOrderAffiliatorStage::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', $this->storeOrderAffiliatorStage::getTag());
    }
}
