<?php

namespace pxlrbt\FilamentExcel\Exports\Formatters;

class ArrayFormatter implements FormatterInterface
{
    public function __construct(
        public string $delimiter = ','
    ) {
        //
    }

    public function shouldApply($state): bool
    {
        return is_array($state);
    }

    public function format($state): string
    {
        return implode(
            $this->delimiter,
            array_map(fn ($value) => app(Formatter::class)->format($value), $state)
        );
    }
}
