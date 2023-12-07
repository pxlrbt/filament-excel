<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Maatwebsite\Excel\Excel;

trait WithWriterType
{
    protected Closure|string|null $writerType = null;

    public function withWriterType(Closure|string|null $writerType = null): static
    {
        $this->writerType = $writerType;

        return $this;
    }

    protected function getWriterType(): ?string
    {
        return $this->evaluate($this->writerType) ?? Excel::XLSX;
    }

    protected function resolveWriterType(): void
    {
        if ($writerType = data_get($this->formData, 'writer_type')) {
            if ($this->writerType instanceof Closure) {
                $writerType = $this->evaluate($this->writerType, ['writerType' => $writerType]);
            }

            $this->withWriterType($writerType);
        }
    }

    protected function getDefaultExtension(): string
    {
        return $this->getWriterType() ? strtolower($this->getWriterType()) : 'xlsx';
    }
}
