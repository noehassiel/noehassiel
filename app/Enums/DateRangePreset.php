<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\Carbon;

enum DateRangePreset: string
{
    case Today = 'today';
    case Yesterday = 'yesterday';
    case ThisWeek = 'this_week';
    case LastWeek = 'last_week';
    case ThisMonth = 'this_month';
    case LastMonth = 'last_month';
    case ThisQuarter = 'this_quarter';
    case LastQuarter = 'last_quarter';
    case ThisYear = 'this_year';
    case LastYear = 'last_year';
    case Last3Days = 'last_3_days';
    case Last7Days = 'last_7_days';
    case Last14Days = 'last_14_days';
    case Last30Days = 'last_30_days';
    case Last90Days = 'last_90_days';
    case Last3Months = 'last_3_months';
    case Last6Months = 'last_6_months';
    case YearToDate = 'year_to_date';
    case LastWeekToDate = 'last_week_to_date';
    case LastMonthToDate = 'last_month_to_date';
    case LastQuarterToDate = 'last_quarter_to_date';
    case Next7Days = 'next_7_days';
    case Next30Days = 'next_30_days';
    case NextMonth = 'next_month';
    case NextQuarter = 'next_quarter';
    case NextYear = 'next_year';
    case Custom = 'custom';

    /**
     * Returns [start, end] as 'Y-m-d' strings (or null for open-ended).
     *
     * @return array{0: string|null, 1: string|null}
     */
    public function resolveRange(): array
    {
        $today = Carbon::today();

        return match ($this) {
            self::Today => [
                $today->format('Y-m-d'),
                $today->format('Y-m-d'),
            ],
            self::Yesterday => [
                $today->subDay()->format('Y-m-d'),
                $today->format('Y-m-d'),
            ],
            self::ThisWeek => [
                Carbon::today()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastWeek => [
                Carbon::today()->subWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                Carbon::today()->subWeek()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d'),
            ],
            self::ThisMonth => [
                Carbon::today()->startOfMonth()->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastMonth => [
                Carbon::today()->subMonth()->startOfMonth()->format('Y-m-d'),
                Carbon::today()->startOfMonth()->subDay()->format('Y-m-d'),
            ],
            self::ThisQuarter => [
                Carbon::today()->startOfQuarter()->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastQuarter => [
                Carbon::today()->subQuarter()->startOfQuarter()->format('Y-m-d'),
                Carbon::today()->subQuarter()->endOfQuarter()->format('Y-m-d'),
            ],
            self::ThisYear => [
                Carbon::today()->startOfYear()->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastYear => [
                Carbon::today()->subYear()->startOfYear()->format('Y-m-d'),
                Carbon::today()->startOfYear()->subDay()->format('Y-m-d'),
            ],
            self::Last3Days => [
                Carbon::today()->subDays(3)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Last7Days => [
                Carbon::today()->subDays(7)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Last14Days => [
                Carbon::today()->subDays(14)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Last30Days => [
                Carbon::today()->subDays(30)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Last90Days => [
                Carbon::today()->subDays(90)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Last3Months => [
                Carbon::today()->subMonths(3)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Last6Months => [
                Carbon::today()->subMonths(6)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::YearToDate => [
                Carbon::today()->startOfYear()->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastWeekToDate => [
                Carbon::today()->subWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastMonthToDate => [
                Carbon::today()->subMonth()->startOfMonth()->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::LastQuarterToDate => [
                Carbon::today()->subQuarter()->startOfQuarter()->format('Y-m-d'),
                Carbon::today()->format('Y-m-d'),
            ],
            self::Next7Days => [
                Carbon::today()->format('Y-m-d'),
                Carbon::today()->addDays(7)->format('Y-m-d'),
            ],
            self::Next30Days => [
                Carbon::today()->format('Y-m-d'),
                Carbon::today()->addDays(30)->format('Y-m-d'),
            ],
            self::NextMonth => [
                Carbon::today()->addMonth()->startOfMonth()->format('Y-m-d'),
                Carbon::today()->addMonth()->endOfMonth()->format('Y-m-d'),
            ],
            self::NextQuarter => [
                Carbon::today()->addQuarter()->startOfQuarter()->format('Y-m-d'),
                Carbon::today()->addQuarter()->endOfQuarter()->format('Y-m-d'),
            ],
            self::NextYear => [
                Carbon::today()->addYear()->startOfYear()->format('Y-m-d'),
                Carbon::today()->addYear()->endOfYear()->format('Y-m-d'),
            ],
            self::Custom => [null, null],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Today => 'Today',
            self::Yesterday => 'Yesterday',
            self::ThisWeek => 'This Week',
            self::LastWeek => 'Last Week',
            self::ThisMonth => 'This Month',
            self::LastMonth => 'Last Month',
            self::ThisQuarter => 'This Quarter',
            self::LastQuarter => 'Last Quarter',
            self::ThisYear => 'This Year',
            self::LastYear => 'Last Year',
            self::Last3Days => 'Last 3 Days',
            self::Last7Days => 'Last 7 Days',
            self::Last14Days => 'Last 14 Days',
            self::Last30Days => 'Last 30 Days',
            self::Last90Days => 'Last 90 Days',
            self::Last3Months => 'Last 3 Months',
            self::Last6Months => 'Last 6 Months',
            self::YearToDate => 'Year to Date',
            self::LastWeekToDate => 'Last Week to Date',
            self::LastMonthToDate => 'Last Month to Date',
            self::LastQuarterToDate => 'Last Quarter to Date',
            self::Next7Days => 'Next 7 Days',
            self::Next30Days => 'Next 30 Days',
            self::NextMonth => 'Next Month',
            self::NextQuarter => 'Next Quarter',
            self::NextYear => 'Next Year',
            self::Custom => 'Custom',
        };
    }

    public static function all(): array
    {
        return array_unique(array_map(fn ($preset) => $preset->value, self::cases()));
    }

    public static function forJs(array $only = []): array
    {
        $cases = empty($only) ?
            self::cases() : array_map(fn (string $key) => self::fromKeyOrFail($key), $only);

        return array_values(array_map(fn (self $case) => (object) [
            'key' => $case->value,
            'label' => $case->label(),
        ], $cases));
    }

    public static function fromKeyOrFail(string $key): self
    {
        return self::tryFrom($key)
            ?? throw new \InvalidArgumentException("Invalid preset: {$key}");
    }

    public function is(self $other): bool
    {
        return $this === $other;
    }
}
