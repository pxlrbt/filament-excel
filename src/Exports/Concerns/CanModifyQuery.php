<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

trait CanModifyQuery
{
    public ?SerializableClosure $modifyQueryUsing = null;

    public bool $useTableQuery = false;

    public function modifyQueryUsing(Closure $callback): static
    {
        $this->modifyQueryUsing = new SerializableClosure($callback);

        return $this;
    }

    public function useTableQuery(bool $useTableQuery = true): static
    {
        $this->useTableQuery = $useTableQuery;

        return $this;
    }
}
