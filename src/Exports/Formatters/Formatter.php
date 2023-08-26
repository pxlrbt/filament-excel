<?php

namespace pxlrbt\FilamentExcel\Exports\Formatters;

use Illuminate\Support\Collection;

class Formatter
{
    protected static $formatters = [
        ArrayFormatter::class,
        EnumFormatter::class,
        ObjectFormatter::class,
    ];

    /**
     * @param  mixed  $state
     * @return mixed
     */
    public function format($state)
    {
        if (is_string($state)) {
            return $state;
        }

        $formatter = $this->getFormatters()
            ->firstWhere(function ($formatter) use ($state) {
                return $formatter->shouldApply($state);
            });

        return $formatter?->format($state) ?? $state;
    }

    private function getFormatters(): Collection
    {
        return collect(static::$formatters)->map(
            fn ($formatter) => app($formatter)
        );
    }
}
