<?php

namespace App\Livewire\Synthesizers;

use App\View\Components\DateRange;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class DateRangeSynthesizer extends Synth
{
    public static $key = 'sheafui-date-range';

    public static function match($target)
    {
        return $target instanceof DateRange;
    }

    public static function unwrapForValidation(DateRange $target)
    {
        $raw = [
            'start' => $target->getStart(),
            'end' => $target->getEnd(),
        ];

        return $raw;
    }

    public static function hydrateFromType($type, $value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return new $type($value['start'] ?? null, $value['end'] ?? null);
    }

    public function dehydrate(DateRange $target)
    {
        $raw = [
            'start' => $target->getStart(),
            'end' => $target->getEnd(),
        ];

        return [$raw, []];
    }

    public function hydrate($value, $meta)
    {
        return static::hydrateFromType(DateRange::class, $value);
    }

    public function get(&$target, $key)
    {
        return match ($key) {
            'start' => $target->getStart(),
            'end' => $target->getEnd(),
        };
    }

    public function set(DateRange &$target, $key, $value)
    {
        $target = match ($key) {
            'start' => DateRange::setStart($value, null),
            'end' => new DateRange($target->getStart(), $value),
        };
    }

    public function unset(DateRange &$target, $key)
    {
        $target = match ($key) {
            'start' => DateRange::setEnd($target->getEnd()),
            'end' => DateRange::setStart($target->getStart()),
        };
    }
}
