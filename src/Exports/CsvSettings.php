<?php

namespace pxlrbt\FilamentExcel\Exports;

readonly class CsvSettings
{
    public function __construct(
        public ?string $delimiter = null,
        public ?string $enclosure = null,
        public ?string $lineEnding = null,
        public ?bool $useBom = null,
        public ?bool $includeSeparatorLine = null,
        public ?bool $excelCompatibility = null,
        public ?string $outputEncoding = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'delimiter' => $this->delimiter,
            'enclosure' => $this->enclosure,
            'line_ending' => $this->lineEnding,
            'use_bom' => $this->useBom,
            'include_separator_line' => $this->includeSeparatorLine,
            'excel_compatibility' => $this->excelCompatibility,
            'output_encoding' => $this->outputEncoding,
        ], fn ($value) => $value !== null);
    }
}
