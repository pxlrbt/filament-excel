<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

trait CanModifyModel
{
    public function model(string $model): static
    {
        $this->model = $model;

        return $this;
    }
}
