<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Illuminate\Support\Arr;

trait WithSheets
{
    protected array|Closure|null $prependedSheets = null;

    protected array|Closure|null $appendedSheets = null;

    protected array|Closure|null $sheets = null;

    public function withSheets(
        array|Closure|null $sheets = null,
        array|Closure $prepend = [],
        array|Closure $append = []
    ): static {
        $this->sheets = $sheets;
        $this->prependedSheets = $prepend;
        $this->appendedSheets = $append;

        return $this;
    }

    public function sheets(): array
    {
        return [
            ...Arr::wrap($this->evaluate($this->prependedSheets) ?? []),
            ...Arr::wrap($this->evaluate($this->sheets) ?? [$this]),
            ...Arr::wrap($this->evaluate($this->appendedSheets) ?? []),
        ];
    }
}
