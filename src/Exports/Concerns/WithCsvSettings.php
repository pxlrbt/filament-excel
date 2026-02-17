<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use pxlrbt\FilamentExcel\Exports\CsvSettings;

trait WithCsvSettings
{
    protected Closure|CsvSettings|array|null $csvSettings = null;

    public function withCsvSettings(Closure|CsvSettings $csvSettings): static
    {
        $this->csvSettings = $csvSettings;

        return $this;
    }

    public function getCsvSettings(): array
    {
        $settings = $this->evaluate($this->csvSettings);

        if ($settings instanceof CsvSettings) {
            return $settings->toArray();
        }

        return $settings ?? [];
    }
}
