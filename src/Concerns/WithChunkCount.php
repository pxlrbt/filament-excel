<?php

namespace pxlrbt\FilamentExcel\Concerns;

trait WithChunkCount
{
    protected int $chunkSize = 100;

    public function withChunkSize(int $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }
}
