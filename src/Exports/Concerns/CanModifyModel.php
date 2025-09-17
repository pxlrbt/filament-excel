<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

trait CanModifyModel
{
  public ?SerializableClosure $modifyModelUsing = null;

  public function modifyModelUsing(string|Closure $model): static
  {
    $this->model = $model;

    return $this;
  }
}
