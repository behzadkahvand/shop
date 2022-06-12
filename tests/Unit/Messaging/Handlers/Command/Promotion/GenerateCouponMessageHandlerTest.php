<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Promotion;

use App\Dictionary\CouponGeneratorInstructionStatus;
use App\Entity\CouponGeneratorInstruction;
use App\Messaging\Handlers\Command\Promotion\GenerateCouponMessageHandler;
use App\Messaging\Messages\Command\Promotion\GenerateCouponMessage;
use App\Repository\CouponGeneratorInstructionRepository;
use App\Service\Promotion\Generator\PromotionCouponGeneratorInterface;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class GenerateCouponMessageHandlerTest extends BaseUnitTestCase
{
    private LegacyMockInterface|CouponGeneratorInstructionRepository|MockInterface|null $repository;

    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $entityManager;

    private LegacyMockInterface|PromotionCouponGeneratorInterface|MockInterface|null $couponGenerator;

    private CouponGeneratorInstruction|LegacyMockInterface|MockInterface|null $instruction;

    private ?GenerateCouponMessage $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository      = Mockery::mock(CouponGeneratorInstructionRepository::class);
        $this->entityManager   = Mockery::mock(EntityManagerInterface::class);
        $this->couponGenerator = Mockery::mock(PromotionCouponGeneratorInterface::class);
        $this->instruction     = Mockery::mock(CouponGeneratorInstruction::class);

        $this->message = new GenerateCouponMessage(1);
    }

    public function testInvoke(): void
    {
        $handler = new GenerateCouponMessageHandler($this->repository, $this->couponGenerator, $this->entityManager);
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($this->instruction);

        $this->instruction->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(CouponGeneratorInstructionStatus::PENDING);
        $this->instruction->shouldReceive('setStatus')->with(CouponGeneratorInstructionStatus::STARTED)->andReturn($this->instruction);
        $this->instruction->shouldReceive('setStatus')->with(CouponGeneratorInstructionStatus::FINISHED)->andReturn($this->instruction);

        $this->entityManager->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->entityManager->shouldReceive('flush')->twice()->withNoArgs();
        $this->entityManager->shouldReceive('commit')->once()->withNoArgs();

        $this->couponGenerator->shouldReceive('generate')->once()->with($this->instruction)->andReturn(['code1']);

        $handler($this->message);
    }

    public function testInvokeWillRollback(): void
    {
        $handler = new GenerateCouponMessageHandler($this->repository, $this->couponGenerator, $this->entityManager);
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($this->instruction);

        $this->instruction->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(CouponGeneratorInstructionStatus::PENDING);
        $this->instruction->shouldReceive('setStatus')->with(CouponGeneratorInstructionStatus::STARTED)->andReturn($this->instruction);
        $this->instruction->shouldReceive('setStatus')->with(CouponGeneratorInstructionStatus::FAILED)->andReturn($this->instruction);

        $this->entityManager->shouldReceive('beginTransaction')->once()->withNoArgs();
        $this->entityManager->shouldReceive('flush')->twice()->withNoArgs();
        $this->entityManager->shouldReceive('rollback')->once()->withNoArgs();
        $this->entityManager->shouldReceive('close')->once()->withNoArgs();

        $this->couponGenerator->shouldReceive('generate')->once()->with($this->instruction)->andThrow(new Exception());

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $handler($this->message);
    }
}
