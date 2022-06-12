<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Order;
use App\Service\Order\AutoConfirm\AutoConfirmOrderServiceInterface;
use App\Service\Order\Stages\AutoConfirmOrderStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

final class AutoConfirmOrderStageTest extends BaseUnitTestCase
{
    private Order|LegacyMockInterface|MockInterface|null $orderMock;

    private AbstractPipelinePayload|LegacyMockInterface|MockInterface|null $payloadMock;

    private LegacyMockInterface|AutoConfirmOrderServiceInterface|MockInterface|null $autoConfirmOrderMock;

    private ?AutoConfirmOrderStage $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock            = Mockery::mock(Order::class);
        $this->payloadMock          = Mockery::mock(AbstractPipelinePayload::class);
        $this->autoConfirmOrderMock = Mockery::mock(AutoConfirmOrderServiceInterface::class);

        $this->sut = new AutoConfirmOrderStage($this->autoConfirmOrderMock);
    }

    public function testGerPriorityAndTag(): void
    {
        self::assertEquals(-35, $this->sut::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', $this->sut::getTag());
    }

    public function testItConfirmOrderAutomaticallyIfOrderIsConfirmable(): void
    {
        $this->payloadMock->shouldReceive(['getOrder' => $this->orderMock])->once()->withNoArgs();
        $this->autoConfirmOrderMock->shouldReceive(['isConfirmable' => true])->once()->with($this->orderMock);
        $this->autoConfirmOrderMock->shouldReceive('confirm')->once()->with($this->orderMock)->andReturn();

        self::assertSame($this->payloadMock, $this->sut->__invoke($this->payloadMock));
    }

    public function testItDontConfirmOrderAutomaticallyIfOrderIsNotConfirmable(): void
    {
        $this->payloadMock->shouldReceive(['getOrder' => $this->orderMock])->once()->withNoArgs();
        $this->autoConfirmOrderMock->shouldReceive(['isConfirmable' => false])->once()->with($this->orderMock);
        $this->autoConfirmOrderMock->shouldNotReceive('confirm');

        self::assertSame($this->payloadMock, $this->sut->__invoke($this->payloadMock));
    }
}
