<?php

namespace pxlrbt\FilamentExcel\Exports\Formatters;

use Filament\Support\Contracts\HasLabel;
use UnitEnum;

class EnumFormatter implements FormatterInterface
{
    public function shouldApply($state): bool
    {
        return function_exists('enum_exists') && $state instanceof UnitEnum;
    }

    public function format($state): string
    {
        if ($state instanceof HasLabel) {
            return $state->getLabel();
        }

        return $state->value;
    }
}
