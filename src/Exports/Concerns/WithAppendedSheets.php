<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;

trait WithAppendedSheets
{
    protected array|Closure|null $appendedSheets = null;

    public function withAppendedSheets(array|Closure $sheets): static
    {
        $this->appendedSheets = $sheets;
        return $this;
    }

    protected function getAppendedSheets(): array
    {
        $sheets = $this->evaluate($this->appendedSheets) ?? [];
        return is_array($sheets) ? $sheets : [$sheets];
    }
}
