<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Closure;
use Illuminate\Support\Str;

trait WithFilename
{
    protected Closure | string | null $filename = null;

    public function withFilename(Closure | string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    protected function getFilename(): ?string
    {
        $filename = $this->evaluate($this->filename) ?? class_basename($this->getModel());

        return $this->ensureFilenameHasExtension($filename);
    }

    abstract protected function getDefaultExtension(): string;

    protected function ensureFilenameHasExtension(string $filename): string
    {
        return Str::contains($filename, '.')
            ? $filename
            : $filename . '.' . $this->getDefaultExtension();
    }

    protected function extractFilename(): void
    {
        if ($filename = data_get($this->formData, 'filename')) {
            $this->withFilename($filename);
        }
    }
}
