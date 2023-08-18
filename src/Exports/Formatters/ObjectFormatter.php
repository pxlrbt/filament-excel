<?php

namespace pxlrbt\FilamentExcel\Exports\Formatters;

class ObjectFormatter implements FormatterInterface
{
    public function shouldApply($state): bool
    {
        return is_object($state) && method_exists($state, '__toString');
    }

    public function format($state): string
    {
        return $state->__toString();
    }
}
