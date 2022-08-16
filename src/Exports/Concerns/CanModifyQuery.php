<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

trait CanModifyQuery
{
    public ?SerializableClosure $modifyQueryUsing = null;

    public function modifyQueryUsing(Closure $callback): static
    {
        $this->modifyQueryUsing = new SerializableClosure($callback);

        return $this;
    }
}
