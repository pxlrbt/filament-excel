<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Illuminate\Support\Str;

trait WithFilename
{
    protected Closure|string|null $filename = null;

    public function withFilename(Closure|string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    protected function getFilename(): ?string
    {
        $filename = $this->evaluate($this->filename) ?? class_basename($this->getModelClass());

        return $this->ensureFilenameHasExtension($filename);
    }

    abstract protected function getDefaultExtension(): string;

    protected function ensureFilenameHasExtension(string $filename): string
    {
        return Str::of($filename)->test('/\.\w{3,4}$/')
            ? $filename
            : $filename.'.'.$this->getDefaultExtension();
    }

    protected function resolveFilename(): void
    {
        if ($filename = data_get($this->formData, 'filename')) {
            if ($this->filename instanceof Closure) {
                $filename = $this->evaluate($this->filename, ['filename' => $filename]);
            }

            $this->withFilename($filename);
        }
    }
}
