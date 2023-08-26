<?php

namespace pxlrbt\FilamentExcel\Exports\Formatters;

use UnitEnum;

class EnumFormatter implements FormatterInterface
{
    public function shouldApply($state): bool
    {
        return function_exists('enum_exists') && $state instanceof UnitEnum;
    }

    public function format($state): string
    {
        return $state->value;
    }
}
