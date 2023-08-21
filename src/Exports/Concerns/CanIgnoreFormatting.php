<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use pxlrbt\FilamentExcel\Columns\Column;

trait CanIgnoreFormatting
{
    public Closure|array|bool $ignoreFormattingOnColumns = false;

    public function ignoreFormatting(Closure|array|bool $columns = true): static
    {
        $this->ignoreFormattingOnColumns = $columns;

        return $this;
    }

    protected function shouldIgnoreFormattingForColumn(Column $column): bool
    {
        $shouldIgnore = $this->evaluate($this->ignoreFormattingOnColumns, [
            'column' => $column,
        ]);

        if (is_bool($shouldIgnore)) {
            return $shouldIgnore;
        }

        return in_array($column->getName(), $shouldIgnore);
    }
}
