<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;

trait CanModifyQuery
{
    public ?Closure $modifyQueryUsing = null;

    public function modifyQueryUsing(Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }
}
