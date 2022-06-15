<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Arr;
use Closure;

trait WithHeadings
{
    public Closure | array | null | bool $autoHeadings = null;

    protected Closure | array | string | null $headings = null;

    public function withHeadings(Closure | array | string | null | bool $headings = null): self
    {
        $this->headings = match (true) {
            is_callable($headings), is_array($headings) => $headings,
            default => func_get_args()
        };

        return $this;
    }

    public function getHeadings()
    {
        return $this->mergeNumericArray(
            $this->evaluate($this->autoHeadings) ?? [],
            $this->evaluate($this->headings) ?? [],
        );
    }

    public function resolveHeadings()
    {
        $keys = $this->getMapping($this->getModelInstance());

        return $this->mergeNumericArray(
            $keys,
            Arr::only($this->getHeadings(), $keys),
        );
    }
}
