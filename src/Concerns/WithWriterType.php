<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Closure;
use Maatwebsite\Excel\Excel;

trait WithWriterType
{
    protected Closure | string | null $writerType = null;

    public function withWriterType(Closure | string | null $writerType = null): self
    {
        $this->writerType = $writerType;

        return $this;
    }

    protected function getWriterType(): ?string
    {
        return $this->evaluate($this->writerType) ?? Excel::XLSX;
    }

    protected function extractWriterType(): void
    {
        if ($writerType = data_get($this->formData, 'writer_type')) {
            $this->withWriterType($writerType);
        }
    }
}
