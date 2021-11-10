<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Service\TimeSharder;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TimeSharderTest extends KernelTestCase
{
    private TimeSharder $timeSharder;

    /**
     * @param array<string, array<array<string>>> $expected
     * @param array<DateTime> $input
     *
     * @dataProvider providerTimeShardingData
     */
    public function testTimeSharding(array $expected, array $input): void
    {
        self::assertSame($expected, $this->timeSharder->shard(...$input));
    }

    /**
     * @return array<array<string, array<array<string>>>>
     */
    public function providerTimeShardingData(): array
    {
        return [
            'hour before month and hour before end month' => [
                [
                    'minute' => [
                        ['2021-02-28 23:00:00', '2021-02-28 23:00:00'],
                    ],
                    'hour' => [
                        ['2020-12-31 23:00:00', '2020-12-31 23:00:00'],
                        ['2021-02-28 00:00:00', '2021-02-28 22:00:00'],
                    ],
                    'day' => [
                        ['2021-02-01 00:00:00', '2021-02-27 00:00:00'],
                    ],
                    'month' => [
                        ['2021-01-01 00:00:00', '2021-01-01 00:00:00'],
                    ],
                ],
                [
                    new DateTime('2020-12-31 23:00:00'),
                    new DateTime('2021-02-28 23:00:00'),
                ],
            ],
            'hour before month and hour before end month seconds after start' => [
                [
                    'minute' => [
                        ['2021-02-28 23:00:00', '2021-02-28 23:00:00'],
                    ],
                    'hour' => [
                        ['2020-12-31 23:00:00', '2020-12-31 23:00:00'],
                        ['2021-02-28 00:00:00', '2021-02-28 22:00:00'],
                    ],
                    'day' => [
                        ['2021-02-01 00:00:00', '2021-02-27 00:00:00'],
                    ],
                    'month' => [
                        ['2021-01-01 00:00:00', '2021-01-01 00:00:00'],
                    ],
                ],
                [
                    new DateTime('2020-12-31 23:00:05'),
                    new DateTime('2021-02-28 23:00:05'),
                ],
            ],
            'hour between not even minutes' => [
                [
                    'minute' => [
                        ['2021-06-03 10:34:00', '2021-06-03 10:59:00'],
                        ['2021-06-03 12:00:00', '2021-06-03 12:36:00'],
                    ],
                    'hour' => [
                        ['2021-06-03 11:00:00', '2021-06-03 11:00:00'],
                    ],
                    'day' => [
                    ],
                    'month' => [
                    ],
                ],
                [
                    new DateTime('2021-06-03 10:34:00'),
                    new DateTime('2021-06-03 12:36:59'),
                ],
            ],
            '2 hours before month end' => [
                [
                    'minute' => [
                        ['2021-06-03 12:00:00', '2021-06-03 12:36:00'],
                    ],
                    'hour' => [
                        ['2021-05-31 22:00:00', '2021-05-31 23:00:00'],
                        ['2021-06-03 00:00:00', '2021-06-03 11:00:00'],
                    ],
                    'day' => [
                        ['2021-06-01 00:00:00', '2021-06-02 00:00:00'],
                    ],
                    'month' => [
                    ],
                ],
                [
                    new DateTime('2021-05-31 22:00:00'),
                    new DateTime('2021-06-03 12:36:59'),
                ],
            ],
            'few minutes' => [
                [
                    'minute' => [
                        ['2021-06-03 12:29:00', '2021-06-03 12:36:00'],
                    ],
                    'hour' => [
                    ],
                    'day' => [
                    ],
                    'month' => [
                    ],
                ],
                [
                    new DateTime('2021-06-03 12:29:00'),
                    new DateTime('2021-06-03 12:36:59'),
                ],
            ],
            'long time' => [
                [
                    'minute' => [
                        ['2021-06-03 12:00:00', '2021-06-03 12:35:00'],
                    ],
                    'hour' => [
                        ['2021-06-03 00:00:00', '2021-06-03 11:00:00'],
                    ],
                    'day' => [
                        ['2021-06-01 00:00:00', '2021-06-02 00:00:00'],
                    ],
                    'month' => [
                        ['1970-01-01 00:00:00', '2021-05-01 00:00:00'],
                    ],
                ],
                [
                    new DateTime('1970-01-01 00:00:00'),
                    new DateTime('2021-06-03 12:35:59'),
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->timeSharder = new TimeSharder();
    }
}
