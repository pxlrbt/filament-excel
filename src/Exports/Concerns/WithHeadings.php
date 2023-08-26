<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use pxlrbt\FilamentExcel\Columns\Column;

trait WithHeadings
{
    public ?array $headings = null;

    protected Closure|bool $withoutHeadings = false;

    protected Closure|bool $withNamesAsHeadings = false;

    public function withoutHeadings(Closure|bool $condition = true): static
    {
        $this->withoutHeadings = $condition;

        return $this;
    }

    public function withNamesAsHeadings(Closure|bool $condition = true): static
    {
        $this->withNamesAsHeadings = $condition;

        return $this;
    }

    public function getHeadings(): array
    {
        if ($this->evaluate($this->withoutHeadings)) {
            return [];
        }

        $namesAsHeadings = $this->evaluate($this->withNamesAsHeadings);

        return $this->getMapping($this->getModelInstance())
            ->map(fn (Column $column) => $namesAsHeadings ? $column->getName() : $this->evaluate($column->getHeading()))
            ->toArray();
    }

    public function headings(): array
    {
        if ($this->headings !== null) {
            return $this->headings;
        }

        return $this->getHeadings();
    }
}
