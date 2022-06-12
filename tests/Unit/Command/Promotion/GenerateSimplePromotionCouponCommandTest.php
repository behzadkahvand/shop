<?php

namespace App\Tests\Unit\Command\Promotion;

use App\Command\Promotion\GenerateSimplePromotionCouponCommand;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\PromotionCouponFormSubmissionHandler;
use App\Service\Promotion\PromotionFormSubmissionHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Form\FormInterface;

class GenerateSimplePromotionCouponCommandTest extends MockeryTestCase
{
    protected PromotionFormSubmissionHandler|LegacyMockInterface|MockInterface|null $promotionFormSubmissionHandlerMock;

    protected PromotionCouponFormSubmissionHandler|LegacyMockInterface|MockInterface|null $promotionCouponFormSubmissionHandlerMock;

    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    protected LegacyMockInterface|MockInterface|FormInterface|null $formMock;

    protected CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionFormSubmissionHandlerMock       = Mockery::mock(PromotionFormSubmissionHandler::class);
        $this->promotionCouponFormSubmissionHandlerMock = Mockery::mock(PromotionCouponFormSubmissionHandler::class);
        $this->em                                       = Mockery::mock(EntityManagerInterface::class);
        $this->formMock                                 = Mockery::mock(FormInterface::class);

        $application = new Application();
        $application->add(new GenerateSimplePromotionCouponCommand(
            $this->promotionFormSubmissionHandlerMock,
            $this->promotionCouponFormSubmissionHandlerMock,
            $this->em
        ));

        $command = $application->find('promotion:coupon:generate-simple');

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->promotionFormSubmissionHandlerMock       = null;
        $this->promotionCouponFormSubmissionHandlerMock = null;
        $this->em                                       = null;
        $this->formMock                                 = null;

        Mockery::close();
    }

    public function testItCanGenerateSimplePromotionCoupon(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->promotionFormSubmissionHandlerMock->shouldReceive('submit')
                                                 ->once()
                                                 ->with(
                                                     Mockery::type(Promotion::class),
                                                     [
                                                         "name"        => "Hello Timcheh",
                                                         "priority"    => 1,
                                                         "couponBased" => true,
                                                         "enabled"     => true,
                                                         "rules"       => [
                                                             0 => [
                                                                 "type"          => "minimum_basket_total",
                                                                 "configuration" => [
                                                                     "basket_total" => 45000
                                                                 ]
                                                             ]
                                                         ],
                                                         "actions"     => [
                                                             0 => [
                                                                 "type"          => "fixed_discount",
                                                                 "configuration" => [
                                                                     "amount" => 40000
                                                                 ]
                                                             ]
                                                         ]
                                                     ]
                                                 )
                                                 ->andReturn($this->formMock);

        $this->formMock->shouldReceive('isValid')
                       ->twice()
                       ->withNoArgs()
                       ->andReturn(true);

        $this->promotionCouponFormSubmissionHandlerMock->shouldReceive('submit')
                                                       ->once()
                                                       ->with(
                                                           Mockery::type(PromotionCoupon::class),
                                                           [
                                                               "code"                  => "hellotimcheh",
                                                               "expiresAt"             => (new DateTime('+3 months'))->format('Y-m-d'),
                                                               "perCustomerUsageLimit" => 1,
                                                               "usageLimit"            => 10,
                                                               "customers"             => [
                                                                   0 => 31
                                                               ]
                                                           ]
                                                       )
                                                       ->andReturn($this->formMock);

        $result = $this->commandTester->execute([
            'name'                   => 'Hello Timcheh',
            'coupon_code'            => 'hellotimcheh',
            'fixed_amount'           => 40000,
            '--minimum-basket-total' => 45000,
            '--max-total-usage'      => 10,
            '--customer-id'          => 31,
        ]);

        self::assertEquals(0, $result);
    }

    public function testItFailsWhenFixedAmountIsNotNumeric(): void
    {
        $result = $this->commandTester->execute([
            'name'                   => 'Hello Timcheh',
            'coupon_code'            => 'hellotimcheh',
            'fixed_amount'           => "ss",
            '--minimum-basket-total' => 45000,
            '--max-total-usage'      => 10,
            '--customer-id'          => 31,
        ]);

        self::assertEquals(1, $result);
    }

    public function testItFailsWhenThereIsAProblemInCreatingPromotion(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('close')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('rollback')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->promotionFormSubmissionHandlerMock->shouldReceive('submit')
                                                 ->once()
                                                 ->with(
                                                     Mockery::type(Promotion::class),
                                                     [
                                                         "name"        => "Hello Timcheh",
                                                         "priority"    => 1,
                                                         "couponBased" => true,
                                                         "enabled"     => true,
                                                         "rules"       => [
                                                             0 => [
                                                                 "type"          => "minimum_basket_total",
                                                                 "configuration" => [
                                                                     "basket_total" => 45000
                                                                 ]
                                                             ]
                                                         ],
                                                         "actions"     => [
                                                             0 => [
                                                                 "type"          => "fixed_discount",
                                                                 "configuration" => [
                                                                     "amount" => 40000
                                                                 ]
                                                             ]
                                                         ]
                                                     ]
                                                 )
                                                 ->andReturn($this->formMock);

        $this->formMock->shouldReceive('isValid')
                       ->once()
                       ->withNoArgs()
                       ->andReturnFalse();

        $result = $this->commandTester->execute([
            'name'                   => 'Hello Timcheh',
            'coupon_code'            => 'hellotimcheh',
            'fixed_amount'           => 40000,
            '--minimum-basket-total' => 45000,
            '--max-total-usage'      => 10,
            '--customer-id'          => 31,
        ]);

        self::assertEquals(1, $result);
    }

    public function testItFailsWhenThereIsAProblemInCreatingPromotionCoupon(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('close')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('rollback')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->promotionFormSubmissionHandlerMock->shouldReceive('submit')
                                                 ->once()
                                                 ->with(
                                                     Mockery::type(Promotion::class),
                                                     [
                                                         "name"        => "Hello Timcheh",
                                                         "priority"    => 1,
                                                         "couponBased" => true,
                                                         "enabled"     => true,
                                                         "rules"       => [
                                                             0 => [
                                                                 "type"          => "minimum_basket_total",
                                                                 "configuration" => [
                                                                     "basket_total" => 45000
                                                                 ]
                                                             ]
                                                         ],
                                                         "actions"     => [
                                                             0 => [
                                                                 "type"          => "fixed_discount",
                                                                 "configuration" => [
                                                                     "amount" => 40000
                                                                 ]
                                                             ]
                                                         ]
                                                     ]
                                                 )
                                                 ->andReturn($this->formMock);

        $this->formMock->shouldReceive('isValid')
                       ->twice()
                       ->withNoArgs()
                       ->andReturn(true, false);

        $this->promotionCouponFormSubmissionHandlerMock->shouldReceive('submit')
                                                       ->once()
                                                       ->with(
                                                           Mockery::type(PromotionCoupon::class),
                                                           [
                                                               "code"                  => "hellotimcheh",
                                                               "expiresAt"             => (new DateTime('+3 months'))->format('Y-m-d'),
                                                               "perCustomerUsageLimit" => 1,
                                                               "usageLimit"            => 10,
                                                               "customers"             => [
                                                                   0 => 31
                                                               ]
                                                           ]
                                                       )
                                                       ->andReturn($this->formMock);

        $result = $this->commandTester->execute([
            'name'                   => 'Hello Timcheh',
            'coupon_code'            => 'hellotimcheh',
            'fixed_amount'           => 40000,
            '--minimum-basket-total' => 45000,
            '--max-total-usage'      => 10,
            '--customer-id'          => 31,
        ]);

        self::assertEquals(1, $result);
    }

    public function testItFailsWhenExceptionOccurs(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('close')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('rollback')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->promotionFormSubmissionHandlerMock->shouldReceive('submit')
                                                 ->once()
                                                 ->with(
                                                     Mockery::type(Promotion::class),
                                                     [
                                                         "name"        => "Hello Timcheh",
                                                         "priority"    => 1,
                                                         "couponBased" => true,
                                                         "enabled"     => true,
                                                         "rules"       => [
                                                             0 => [
                                                                 "type"          => "minimum_basket_total",
                                                                 "configuration" => [
                                                                     "basket_total" => 45000
                                                                 ]
                                                             ]
                                                         ],
                                                         "actions"     => [
                                                             0 => [
                                                                 "type"          => "fixed_discount",
                                                                 "configuration" => [
                                                                     "amount" => 40000
                                                                 ]
                                                             ]
                                                         ]
                                                     ]
                                                 )
                                                 ->andReturn($this->formMock);

        $this->formMock->shouldReceive('isValid')
                       ->once()
                       ->withNoArgs()
                       ->andReturnTrue();

        $this->promotionCouponFormSubmissionHandlerMock->shouldReceive('submit')
                                                       ->once()
                                                       ->with(
                                                           Mockery::type(PromotionCoupon::class),
                                                           [
                                                               "code"                  => "hellotimcheh",
                                                               "expiresAt"             => (new DateTime('+3 months'))->format('Y-m-d'),
                                                               "perCustomerUsageLimit" => 1,
                                                               "usageLimit"            => 10,
                                                               "customers"             => [
                                                                   0 => 31
                                                               ]
                                                           ]
                                                       )
                                                       ->andThrows(new Exception());

        $result = $this->commandTester->execute([
            'name'                   => 'Hello Timcheh',
            'coupon_code'            => 'hellotimcheh',
            'fixed_amount'           => 40000,
            '--minimum-basket-total' => 45000,
            '--max-total-usage'      => 10,
            '--customer-id'          => 31,
        ]);

        self::assertEquals(1, $result);
    }
}
