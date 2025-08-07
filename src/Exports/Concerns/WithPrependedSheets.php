<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;

trait WithPrependedSheets
{
    protected array|Closure|null $prependedSheets = null;

    public function withPrependedSheets(array|Closure $sheets): static
    {
        $this->prependedSheets = $sheets;
        return $this;
    }

    protected function getPrependedSheets(): array
    {
        $sheets = $this->evaluate($this->prependedSheets) ?? [];
        return is_array($sheets) ? $sheets : [$sheets];
    }
}
