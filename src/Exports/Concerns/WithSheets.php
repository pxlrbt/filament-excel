<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;

trait WithSheets
{
    protected array|Closure|null $prependedSheets = null;
    protected array|Closure|null $appendedSheets = null;
    protected array|Closure|null $sheets = null;

    public function withSheets(array|Closure|null $sheets = null, array|Closure $prepend = [], array|Closure $append = []): static
    {
        $this->sheets = $sheets;
        $this->prependedSheets = $prepend;
        $this->appendedSheets = $append;

        return $this;
    }

    protected function getSheets(): array
    {
        $sheets = $this->evaluate($this->sheets) ?? [];
        return is_array($sheets) ? $sheets : [$sheets];
    }

    protected function getPrependedSheets(): array
    {
        $prependedSheets = $this->evaluate($this->prependedSheets) ?? [];
        return is_array($prependedSheets) ? $prependedSheets : [$prependedSheets];
    }

    protected function getAppendedSheets(): array
    {
        $appendedSheets = $this->evaluate($this->appendedSheets) ?? [];
        return is_array($appendedSheets) ? $appendedSheets : [$appendedSheets];
    }
}
