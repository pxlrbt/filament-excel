<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Maatwebsite\Excel\Excel;

trait WithWriterType
{
    protected ?string $writerType = null;

    public function withWriterType(?string $writerType = null): self
    {
        $this->writerType = $writerType;

        return $this;
    }

    protected function getWriterType(): ?string
    {
        return $this->writerType ?? Excel::XLSX;
    }

    protected function handleWriterType(array $data): void
    {
        if ($writerType = data_get($data, 'writerType')) {
            $this->withWriterType($writerType);
        }
    }
}
