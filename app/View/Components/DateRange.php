<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\DateRangePreset;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\Exceptions\InvalidFormatException;
use InvalidArgumentException;

class DateRange extends CarbonPeriod
{
    protected DateRangePreset $preset = DateRangePreset::Custom;

    public function __construct(Carbon|string|null $start = null, Carbon|string|null $end = null)
    {
        $start = $this->parseDate($start);
        $end = $this->parseDate($end);

        parent::__construct($start, $end);
    }

    protected function parseDate(Carbon|string|null $date): ?Carbon
    {
        if ($date === null) {
            return null;
        }
        if ($date instanceof Carbon) {
            return $date;
        }

        if (! is_string($date)) {
            throw new InvalidArgumentException(
                sprintf('Expected string or Carbon, got %s', gettype($date))
            );
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (InvalidFormatException $e) {
            throw new InvalidArgumentException(
                sprintf("Invalid date format. Expected 'YYYY-MM-DD', got '%s'.", $date),
                previous: $e
            );
        }
    }

    public function getStart(): ?string
    {
        return $this->getStartDate()?->format('Y-m-d');
    }

    public function getEnd(): ?string
    {
        return $this->getEndDate()?->format('Y-m-d');
    }

    public function hasStart(): bool
    {
        return $this->getStartDate() !== null;
    }

    public function hasEnd(): bool
    {
        return $this->getEndDate() !== null;
    }

    public function getPreset(): DateRangePreset
    {
        return $this->preset;
    }

    public function preset(DateRangePreset $preset): void
    {
        $this->preset = $preset;
    }

    public static function setStart(Carbon|string|null $start): static
    {
        return new static($start, null);
    }

    public static function setEnd(Carbon|string|null $end): static
    {
        return new static(null, $end);
    }

    public static function fromPreset(DateRangePreset $preset): static
    {
        [$start, $end] = $preset->resolveRange();
        $instance = new static($start, $end);
        $instance->preset($preset);

        return $instance;
    }

    public static function today(): static
    {
        return static::fromPreset(DateRangePreset::Today);
    }

    public static function yesterday(): static
    {
        return static::fromPreset(DateRangePreset::Yesterday);
    }

    public static function thisWeek(): static
    {
        return static::fromPreset(DateRangePreset::ThisWeek);
    }

    public static function lastWeek(): static
    {
        return static::fromPreset(DateRangePreset::LastWeek);
    }

    public static function thisMonth(): static
    {
        return static::fromPreset(DateRangePreset::ThisMonth);
    }

    public static function lastMonth(): static
    {
        return static::fromPreset(DateRangePreset::LastMonth);
    }

    public static function thisQuarter(): static
    {
        return static::fromPreset(DateRangePreset::ThisQuarter);
    }

    public static function lastQuarter(): static
    {
        return static::fromPreset(DateRangePreset::LastQuarter);
    }

    public static function thisYear(): static
    {
        return static::fromPreset(DateRangePreset::ThisYear);
    }

    public static function lastYear(): static
    {
        return static::fromPreset(DateRangePreset::LastYear);
    }

    public static function last3Days(): static
    {
        return static::fromPreset(DateRangePreset::Last3Days);
    }

    public static function last7Days(): static
    {
        return static::fromPreset(DateRangePreset::Last7Days);
    }

    public static function last14Days(): static
    {
        return static::fromPreset(DateRangePreset::Last14Days);
    }

    public static function last30Days(): static
    {
        return static::fromPreset(DateRangePreset::Last30Days);
    }

    public static function last90Days(): static
    {
        return static::fromPreset(DateRangePreset::Last90Days);
    }

    public static function last3Months(): static
    {
        return static::fromPreset(DateRangePreset::Last3Months);
    }

    public static function last6Months(): static
    {
        return static::fromPreset(DateRangePreset::Last6Months);
    }

    public static function yearToDate(): static
    {
        return static::fromPreset(DateRangePreset::YearToDate);
    }

    public static function lastWeekToDate(): static
    {
        return static::fromPreset(DateRangePreset::LastWeekToDate);
    }

    public static function lastMonthToDate(): static
    {
        return static::fromPreset(DateRangePreset::LastMonthToDate);
    }

    public static function lastQuarterToDate(): static
    {
        return static::fromPreset(DateRangePreset::LastQuarterToDate);
    }

    public static function next7Days(): static
    {
        return static::fromPreset(DateRangePreset::Next7Days);
    }

    public static function next30Days(): static
    {
        return static::fromPreset(DateRangePreset::Next30Days);
    }

    public static function nextMonth(): static
    {
        return static::fromPreset(DateRangePreset::NextMonth);
    }

    public static function nextQuarter(): static
    {
        return static::fromPreset(DateRangePreset::NextQuarter);
    }

    public static function nextYear(): static
    {
        return static::fromPreset(DateRangePreset::NextYear);
    }
}
