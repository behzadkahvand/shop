<?php

namespace App\Tests\Unit\Service\ORM\Extension;

use App\Service\ORM\Extension\SortParameterNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Class SortParameterNormalizerTest
 */
final class SortParameterNormalizerTest extends TestCase
{
    public function testItNormalizeSortData()
    {
        $sorts = ['foo.bar', '-foo.bar'];

        foreach (new SortParameterNormalizer($sorts) as $key => $data) {
            self::assertArrayHasKey('field', $data);
            self::assertEquals('foo.bar', $data['field']);
            self::assertArrayHasKey('direction', $data);
            self::assertArrayHasKey('direction_prefix', $data);

            if (0 === $key) {
                self::assertEquals('ASC', $data['direction']);
                self::assertEquals('', $data['direction_prefix']);
            } else {
                self::assertEquals('DESC', $data['direction']);
                self::assertEquals('-', $data['direction_prefix']);
            }
        }
    }

    public function testStaticToArrayMethod()
    {
        $expected = [
            [
                'field' => 'foo.bar',
                'direction' => 'ASC',
                'direction_prefix' => '',
            ],
            [
                'field' => 'foo.bar',
                'direction' => 'DESC',
                'direction_prefix' => '-',
            ],
        ];

        self::assertEquals($expected, SortParameterNormalizer::toArray(['foo.bar', '-foo.bar']));
    }
}
