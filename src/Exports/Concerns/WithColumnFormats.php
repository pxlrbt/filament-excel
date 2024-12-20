<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use pxlrbt\FilamentExcel\Columns\Column;

trait WithColumnFormats
{
    protected ?array $columnFormats = null;

    public function columnFormats(): array
    {
        return $this->getColumnFormats();
    }

    public function getColumnFormats(): array
    {
        return $this->columnFormats ??= $this->getMapping($this->getModelInstance())
            ->values()
            ->mapWithKeys(fn (Column $column, $key) => [
                Coordinate::stringFromColumnIndex($key + 1) => $this->evaluate($column->getFormat()),
            ])
            ->filter()
            ->toArray();
    }
}
