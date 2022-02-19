<?php

namespace pxlrbt\FilamentExcel\Concerns;

trait WithExportable
{
    protected ?string $exportable = null;

    public function withExportable($exportable): self
    {
        $this->exportable = $exportable;

        return $this;
    }

    public function getExportable(): object
    {
        return $this->exportable === null
            ? $this
            : app($this->exportable, [
                'action' => $this->action,
                'resource' => $this->resource,
                'model' => $this->model,
                'records' => $this->records,
            ]);
    }
}
