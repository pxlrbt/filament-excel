<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;

trait Only
{
    protected Closure|array|null $only = [];

    public function only(Closure|array|string $columns): static
    {
        $this->only = match (true) {
            is_callable($columns), is_array($columns) => $columns,
            default => func_get_args()
        };

        return $this;
    }

    public function getOnly(): array
    {
        return $this->evaluate($this->only);
    }
}
