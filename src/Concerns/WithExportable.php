<?php

namespace pxlrbt\FilamentExcel\Concerns;

trait WithExportable
{
    protected object $exportable;

    public function withExportable($exportable): self
    {
        $this->exportable = $exportable;

        return $this;
    }

    public function getExportable(): object
    {
        return $this->exportable ?? $this;
    }
}
