<?php

namespace App\Tests\Unit\Service\Promotion\Rule;

use App\Entity\City;
use App\Entity\CustomerAddress;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\CityRuleType;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\AbstractType;

class CityRuleTypeTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function data(): Generator
    {
        yield [1, ['city_ids' => [1, 2]], true];
        yield [1, ['city_ids' => [3, 4]], false];
    }

    /**
     * @dataProvider data
     */
    public function testIsValid($subjectCityId, $configurations, $result): void
    {
        $city = Mockery::mock(City::class);
        $city->shouldReceive('getId')->once()->withNoArgs()->andReturn($subjectCityId);
        $address = Mockery::mock(CustomerAddress::class);
        $address->shouldReceive('getCity')->once()->withNoArgs()->andreturn($city);

        $subject = Mockery::mock(PromotionSubjectInterface::class);
        $subject->shouldReceive('getAddress')->once()->withNoArgs()->andReturn($address);

        $rule = new CityRuleType();

        self::assertEquals($result, $rule->isValid($subject, $configurations));
    }

    /**
     * @dataProvider data
     */
    public function testIsValidWithoutAddress(): void
    {
        $subject = Mockery::mock(PromotionSubjectInterface::class);
        $subject->shouldReceive('getAddress')->once()->withNoArgs()->andReturnNull();

        $rule = new CityRuleType();

        self::assertFalse($rule->isValid($subject, ['city_ids' => [1]]));
    }

    public function testGetName(): void
    {
        self::assertIsString(CityRuleType::getName());
    }

    public function testConfigurationFormType(): void
    {
        $ruleType = new CityRuleType();
        self::assertTrue(is_subclass_of($ruleType->getConfigurationFormType(), AbstractType::class));
    }
}
