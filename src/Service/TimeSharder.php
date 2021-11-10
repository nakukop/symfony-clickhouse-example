<?php

declare(strict_types=1);

namespace App\Service;

use Carbon\Carbon;
use DateTime;

class TimeSharder
{
    /**
     * @return array<string, array<array<string>>>
     */
    public function shard(DateTime $dateFrom, DateTime $dateTo): array
    {
        $dateFrom = new Carbon($dateFrom);
        $dateTo = new Carbon($dateTo);
        $currentDate = new Carbon();

        $dateFrom->startOfMinute();
        $dateTo->endOfMinute();

        if ($dateFrom->greaterThan($dateTo)) {
            $buffer = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $buffer;
            unset($buffer);
        }

        if ($dateTo->greaterThan($currentDate)) {
            $dateTo = $currentDate;
        }

        return [
            'minute' => $this->getMinuteShards($dateFrom, $dateTo),
            'hour' => $this->getHourShards($dateFrom, $dateTo),
            'day' => $this->getDayShards($dateFrom, $dateTo),
            'month' => $this->getMonthShards($dateFrom, $dateTo),
        ];
    }

    /**
     * @return array<array<string>>
     */
    private function getMonthShards(Carbon $from, Carbon $to): array
    {
        $shards = [];
        $format = 'Y-m-d 00:00:00';

        if (
            $from->diffInMonths($to) > 0
            || (
                $from->month === $to->month
                && $from->equalTo($from->clone()->startOfMonth())
                && $to->equalTo($to->clone()->endOfMonth())
            )
        ) {
            $shards[] = [
                $from->startOfMonth()->format($format),
                $to->startOfMonth()->format($format),
            ];
        }

        return $shards;
    }

    /**
     * @return array<array<string>>
     */
    private function getDayShards(Carbon $from, Carbon $to): array
    {
        $shards = [];
        $format = 'Y-m-d 00:00:00';

        if ($from->diffInDays($to) > 0) {
            if (!$from->clone()->endOfMonth()->greaterThan($to)) {
                if (!$from->equalTo($from->clone()->startOfMonth())) {
                    $shards[] = [
                        $from->format($format),
                        $from->clone()->endOfMonth()->format($format),
                    ];

                    $from->modify('+1 month')->startOfMonth();
                }

                if (!$to->equalTo($to->clone()->endOfMonth())) {
                    $shards[] = [
                        $to->clone()->startOfMonth()->format($format),
                        $to->format($format),
                    ];

                    $to->modify('-1 month')->endOfMonth();
                }
            } else {
                $shards[] = [
                    $from->format($format),
                    $to->format($format),
                ];
            }
        } elseif (
            $from->hour === $to->hour
            && $from->equalTo($from->clone()->startOfDay())
            && $to->equalTo($to->clone()->endOfDay())
        ) {
            $shards[] = [
                $from->format($format),
                $to->format($format),
            ];
        }

        return $shards;
    }

    /**
     * @return array<array<string>>
     */
    private function getHourShards(Carbon $from, Carbon $to): array
    {
        $shards = [];
        $format = 'Y-m-d H:00:00';

        if ($from->diffInHours($to) > 0) {
            if (!$from->clone()->endOfDay()->greaterThan($to)) {
                if (!$from->equalTo($from->clone()->startOfDay())) {
                    $shards[] = [
                        $from->format($format),
                        $from->clone()->endOfDay()->format($format),
                    ];

                    $from->modify('+1 day')->startOfDay();
                }

                if (!$to->equalTo($to->clone()->endOfDay())) {
                    $shards[] = [
                        $to->clone()->startOfDay()->format($format),
                        $to->format($format),
                    ];

                    $to->modify('-1 day')->endOfDay();
                }
            } else {
                $shards[] = [
                    $from->format($format),
                    $to->format($format),
                ];
            }
        } elseif (
            $from->hour === $to->hour
            && $from->equalTo($from->clone()->startOfHour())
            && $to->equalTo($to->clone()->endOfHour())
        ) {
            $shards[] = [
                $from->format($format),
                $to->format($format),
            ];
        }

        return $shards;
    }

    /**
     * @return array<array<string>>
     */
    private function getMinuteShards(Carbon $from, Carbon $to): array
    {
        $shards = [];
        $format = 'Y-m-d H:i:00';

        if (!$from->clone()->endOfHour()->greaterThan($to)) {
            if (!$from->equalTo($from->clone()->startOfHour())) {
                $shards[] = [
                    $from->format($format),
                    $from->clone()->endOfHour()->format($format),
                ];

                $from->modify('+1 hour')->startOfHour();
            }

            if (!$to->equalTo($to->clone()->endOfHour())) {
                $shards[] = [
                    $to->clone()->startOfHour()->format($format),
                    $to->format($format),
                ];

                $to->modify('-1 hour')->endOfHour();
            }
        } else {
            $shards[] = [
                $from->format($format),
                $to->format($format),
            ];
        }

        return $shards;
    }
}
