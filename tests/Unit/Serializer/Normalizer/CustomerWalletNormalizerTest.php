<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\Wallet;
use App\Serializer\Normalizer\CustomerWalletNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerWalletNormalizerTest extends MockeryTestCase
{
    private ObjectNormalizer|LegacyMockInterface|MockInterface|null $normalizer;
    private LegacyMockInterface|MockInterface|WebsiteAreaService|null $websiteAreaService;
    private LegacyMockInterface|MockInterface|Wallet|null $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer         = Mockery::mock(ObjectNormalizer::class);
        $this->websiteAreaService = Mockery::mock(WebsiteAreaService::class);
        $this->wallet = Mockery::mock(Wallet::class);

        $this->sut = new CustomerWalletNormalizer(
            $this->websiteAreaService,
            $this->normalizer
        );
    }

    public function testShouldNotSupportNormalizationIfAreaIsNotCustomer(): void
    {
        $this->websiteAreaService->expects('isCustomerArea')
                                     ->withNoArgs()
                                     ->andReturnFalse();

        self::assertFalse($this->sut->supportsNormalization($this->wallet, 'json'));
    }

    public function testShouldNotSupportNormalizationIfObjectIsNotOfTypeWallet()
    {
        $this->websiteAreaService->expects('isCustomerArea')
                                     ->withNoArgs()
                                     ->andReturnTrue();

        self::assertFalse($this->sut->supportsNormalization(new stdClass(), 'json'));
    }

    public function testShouldSetBalanceToZeroIfFrozenIsTrue(): void
    {
        $data = [
            'isFrozen' => true,
            'balance' => 10000
        ];

        $this->normalizer->expects('normalize')
            ->with($this->wallet, null, [])
            ->andReturn($data);

        $actual = $this->sut->normalize($this->wallet);

        self::assertEquals(0, $actual['balance']);
    }

    public function testShouldNotModifyBalanceIfFrozenIsFalse(): void
    {
        $expected = 1000;

        $data = [
            'isFrozen' => false,
            'balance' => $expected
        ];

        $this->normalizer->expects('normalize')
            ->with($this->wallet, null, [])
            ->andReturn($data);

        $actual = $this->sut->normalize($this->wallet);

        self::assertEquals($expected, $actual['balance']);
    }

    public function testShouldRemoveIsFrozenFromData(): void
    {
        $expected = 1000;

        $data = [
            'isFrozen' => false,
            'balance' => $expected
        ];

        $this->normalizer->expects('normalize')
            ->with($this->wallet, null, [])
            ->andReturn($data);

        $actual = $this->sut->normalize($this->wallet);

        self::assertArrayNotHasKey('isFrozen', $actual);
    }
}
