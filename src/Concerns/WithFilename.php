<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Illuminate\Support\Str;

trait WithFilename
{
    protected ?string $filename = null;

    public function withFilename(?string $filename = null): self
    {
        $this->filename = $filename;

        return $this;
    }

    protected function getFilename(): ?string
    {
        $filename = $this->filename ?? class_basename($this->model);

        return $this->ensureFilenameHasExtension($filename);
    }

    abstract protected function getDefaultExtension(): string;

    protected function ensureFilenameHasExtension(string $filename): string
    {
        return Str::contains($filename, '.')
            ? $filename
            : $filename . '.' . $this->getDefaultExtension();
    }

    protected function handleFilename(array $data): void
    {
        if ($filename = data_get($data, 'filename')) {
            $this->withFilename($filename);
        }
    }
}
